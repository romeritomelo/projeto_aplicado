(() => {
'use strict';


const $ = (s) => document.querySelector(s);

const money = new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
});

const nav = $('.nav');
const menu = $('.menu');

menu.addEventListener('click', () => {
    nav.classList.toggle('open');
});

document.querySelectorAll('.links a').forEach((a) => {
    a.addEventListener('click', () => {
        nav.classList.remove('open');
    });
});

const observer = new IntersectionObserver(
    (es) => es.forEach((e) => {
        if (e.isIntersecting) {
            e.target.classList.add('visible');
            observer.unobserve(e.target);
        }
    }),
    {
        threshold: 0.12
    }
);

document.querySelectorAll('.reveal').forEach((e) => {
    observer.observe(e);
});

const form = $('#form');
const initial = $('#initial');
const monthly = $('#monthly');
const rate = $('#rate');
const months = $('#months');
const invested = $('#invested');
const earnings = $('#earnings');
const future = $('#future');
const canvas = $('#growth');
const ctx = canvas.getContext('2d');

function simulate() {
    const start = Math.max(0, +initial.value || 0);
    const aporte = Math.max(0, +monthly.value || 0);
    const taxa = Math.max(0, +rate.value || 0) / 100;
    const n = Math.min(600, Math.max(1, +months.value || 1));

    let saldo = start;
    const data = [saldo];

    for (let i = 0; i < n; i++) {
        saldo = saldo * (1 + taxa) + aporte;
        data.push(saldo);
    }

    invested.textContent = money.format(start + aporte * n);
    earnings.textContent = money.format(
        Math.max(0, saldo - (start + aporte * n))
    );
    future.textContent = money.format(saldo);

    draw(data);
}

function draw(data) {
    const dpr = devicePixelRatio || 1;
    const r = canvas.getBoundingClientRect();
    const w = Math.max(280, r.width);
    const h = 190;

    canvas.width = w * dpr;
    canvas.height = h * dpr;

    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    ctx.clearRect(0, 0, w, h);

    const max = Math.max(...data, 1);
    const pad = 8;
    const ch = h - 30;

    ctx.strokeStyle = '#e7edf5';
    ctx.setLineDash([4, 5]);

    [0.25, 0.5, 0.75, 1].forEach((v) => {
        const y = 10 + ch * (1 - v);

        ctx.beginPath();
        ctx.moveTo(pad, y);
        ctx.lineTo(w - pad, y);
        ctx.stroke();
    });

    ctx.setLineDash([]);

    const pts = data.map((v, i) => ({
        x: pad + (i / (data.length - 1)) * (w - 2 * pad),
        y: 10 + ch - (v / max) * ch
    }));

    const g = ctx.createLinearGradient(0, 10, 0, h);

    g.addColorStop(0, 'rgba(37,99,235,.24)');
    g.addColorStop(1, 'rgba(37,99,235,0)');

    ctx.beginPath();
    ctx.moveTo(pts[0].x, h - 20);

    pts.forEach((p) => {
        ctx.lineTo(p.x, p.y);
    });

    ctx.lineTo(pts.at(-1).x, h - 20);
    ctx.closePath();

    ctx.fillStyle = g;
    ctx.fill();

    ctx.beginPath();

    pts.forEach((p, i) => {
        if (i) {
            ctx.lineTo(p.x, p.y);
        } else {
            ctx.moveTo(p.x, p.y);
        }
    });

    ctx.strokeStyle = '#2563eb';
    ctx.lineWidth = 3;
    ctx.stroke();

    const p = pts.at(-1);

    ctx.beginPath();
    ctx.arc(p.x, p.y, 4, 0, Math.PI * 2);
    ctx.fillStyle = '#2563eb';
    ctx.fill();
}

form.addEventListener('submit', (e) => {
    e.preventDefault();
    simulate();
});

[initial, monthly, rate, months].forEach((x) => {
    x.addEventListener('input', simulate);
});

addEventListener('resize', simulate);

simulate();

let pct = 64;

$('#advance').addEventListener('click', () => {
    pct = pct >= 100 ? 10 : Math.min(100, pct + 5);

    $('#progress').style.width = pct + '%';
    $('#percent').textContent = pct + '%';
});

$('#year').textContent = new Date().getFullYear();


})();
