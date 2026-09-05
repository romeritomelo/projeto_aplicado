# Finanças+

Aplicação web desenvolvida como Projeto Aplicado da Pós-Graduação EAD em Segurança da Informação e Análise Forense da UNCISAL.

O projeto consiste em uma aplicação web de controle financeiro com autenticação de usuários e mecanismos de segurança implementados tanto na aplicação quanto na infraestrutura de hospedagem.

O desenvolvimento foi realizado seguindo princípios de **Secure by Design** e **Secure by Default**, contemplando segurança da aplicação, proteção da infraestrutura, controle de acesso, proteção de credenciais, versionamento seguro e implantação automatizada em ambiente de nuvem.

---

# 📌 Visão Geral do Projeto

O projeto está estruturado em três eixos fundamentais, integrados por uma esteira de automação:

```text
┌──────────────────────────────────────────────────────────────┐
│ EIXO 3 — DESENVOLVIMENTO                                    │
│ Aplicação Web                                                │
│                                                              │
│ Login → Autenticação → Página Interna → Logout              │
└────────────────────────────┬─────────────────────────────────┘
                             │
                       Commit & Push
                             │
                             ▼
┌──────────────────────────────────────────────────────────────┐
│ EIXO 2 — REPOSITÓRIO                                         │
│ GitHub público                                               │
│                                                              │
│ Versionamento + Segurança + GitHub Secrets                   │
└────────────────────────────┬─────────────────────────────────┘
                             │
                       GitHub Actions
                             │
                             ▼
┌──────────────────────────────────────────────────────────────┐
│ EIXO 1 — INFRAESTRUTURA                                      │
│ Oracle Cloud Infrastructure                                  │
│                                                              │
│ Traefik → Apache/PHP → Aplicação → MySQL HeatWave           │
└──────────────────────────────────────────────────────────────┘
```

A aplicação está hospedada na **Oracle Cloud Infrastructure (OCI)** e está disponível publicamente através de HTTPS.

---

# ☁️ Eixo 1: Infraestrutura — Cloud Computing

## 🛠️ Hospedagem e Infraestrutura

### Provedor de nuvem

Foi utilizada a **Oracle Cloud Infrastructure (OCI)**, utilizando recursos compatíveis com o Free Tier.

### Sistema operacional

A VM de produção utiliza:

```text
Oracle Linux 9.8
```

A utilização do Oracle Linux 9.8 foi mantida no projeto mediante autorização acadêmica para utilização da infraestrutura já configurada.

### Servidor Web

A aplicação utiliza:

```text
Apache/PHP
```

O acesso externo é intermediado pelo **Traefik**, que atua como proxy reverso e termina as conexões HTTPS.

### Containerização

A infraestrutura utiliza Docker para execução dos serviços.

Principais componentes:

* Docker 29.7.2;
* Traefik 3.7.11;
* Apache/PHP;
* MySQL HeatWave.

### Banco de dados

A aplicação utiliza **MySQL HeatWave**, disponibilizado em uma subnet privada da VCN da OCI.

As credenciais reais do banco de dados não são armazenadas no repositório público.

O arquivo:

```text
config/database.php
```

contém somente uma referência para o arquivo real de configuração localizado fora da raiz da aplicação:

```text
/home/opc/database.php
```

Dessa forma, as credenciais de conexão não são disponibilizadas no código público.

---

## 🌐 Disponibilidade

A aplicação está disponível publicamente através do domínio:

```text
https://romeritomelo.seg.br
```

A aplicação também possui endereço IP público na infraestrutura da OCI.

O acesso externo é realizado através do Traefik, que encaminha as requisições para o Apache/PHP.

---

## 🛡️ Segurança da infraestrutura

Foram implementados mecanismos de segurança em diferentes camadas:

* acesso administrativo por chave SSH;
* autenticação SSH por senha desabilitada;
* Firewalld;
* Fail2Ban;
* proteção da porta SSH;
* HTTPS;
* HSTS;
* TLS 1.2 e TLS 1.3;
* HTTP/2;
* HTTP/3 sobre QUIC;
* Forward Secrecy;
* suporte a mecanismos de troca de chaves pós-quânticos;
* credenciais do banco de dados fora do repositório;
* GitHub Secrets para credenciais utilizadas pelo pipeline.

### SSH

O acesso administrativo à VM utiliza autenticação por chave SSH.

A autenticação por senha no SSH foi desabilitada.

---

## 🔥 Firewall e controle de acesso

O **Firewalld** é utilizado para controle do tráfego da VM.

A infraestrutura mantém abertas somente as portas necessárias ao funcionamento e administração controlada do ambiente, incluindo:

```text
TCP 80   → HTTP
TCP 22   → SSH
UDP 443  → HTTP/3 / QUIC
```

A porta SSH é protegida adicionalmente pelo Fail2Ban.

---

## 🚫 Fail2Ban

A porta de gerenciamento SSH, TCP 22, está protegida pelo Fail2Ban.

Configuração utilizada:

```text
maxretry = 4
findtime = 10 minutos
bantime = 24 horas
```

Dessa forma, após quatro tentativas de autenticação malsucedidas dentro da janela configurada, o endereço IP é bloqueado durante 24 horas.

O Fail2Ban utiliza integração com o Firewalld para realizar os bloqueios.

---

# 🔐 HTTPS, TLS e Certificado

A aplicação utiliza HTTPS com certificado digital emitido pelo **Let's Encrypt**.

A emissão e renovação do certificado são gerenciadas automaticamente pelo **Traefik**.

> A infraestrutura utiliza Traefik para gerenciamento do HTTPS/Let's Encrypt, em vez de uma instalação independente do Certbot.

Foi escolhido Traefik por englobar a função do Certbot, a utilização de domínio/subdominios configurados e a integração com Docker.

O servidor realiza o redirecionamento do tráfego HTTP para HTTPS.

---

## TLS

A configuração TLS disponibiliza:

* TLS 1.2;
* TLS 1.3;
* Forward Secrecy;
* SNI;
* HTTP/2;
* HTTP/3/QUIC.

Protocolos obsoletos como:

```text
SSL 2
SSL 3
TLS 1.0
TLS 1.1
```

estão desabilitados.

---

## HSTS

A aplicação implementa HTTP Strict Transport Security (HSTS).

A política utilizada é:

```http
Strict-Transport-Security: max-age=31536000; includeSubDomains
```

Isso determina que navegadores utilizem HTTPS durante o período de um ano e aplica a política aos subdomínios.

---

## HTTP/2 e HTTP/3

O ambiente suporta HTTP/2 e anuncia HTTP/3 por meio do protocolo QUIC.

O HTTPS é terminado no Traefik, que atua como proxy reverso para o Apache/PHP.

---

## SNI

O ambiente utiliza **Server Name Indication (SNI)** para permitir a seleção do certificado correspondente ao domínio durante o handshake TLS.

---

## Criptografia pós-quântica — PQC

A avaliação externa identificou suporte a mecanismos híbridos de troca de chaves pós-quânticos, incluindo:

```text
X25519MLKEM768
```

quando suportados pelo cliente.

---

# 🧪 Validação da configuração TLS — Qualys SSL Labs

A configuração HTTPS/TLS foi submetida ao **Qualys SSL Labs SSL Server Test**.

### Resultado

```text
A+
```

A avaliação identificou:

* certificado confiável emitido pelo Let's Encrypt;
* certificado RSA de 4096 bits;
* TLS 1.2 e TLS 1.3;
* Forward Secrecy;
* HSTS;
* SNI;
* HTTP/2;
* suporte a HTTP/3;
* suporte a mecanismos de troca de chaves pós-quânticos;
* ausência de vulnerabilidades TLS históricas relevantes identificadas pelo teste.

O resultado A+ corresponde à configuração avaliada no momento do teste e pode variar após alterações na infraestrutura, certificados ou políticas de segurança.

---

# 📦 Eixo 2: Repositório — Hospedagem e Versionamento

## 🛠️ Repositório

O código-fonte está hospedado publicamente no GitHub:

```text
https://github.com/romeritomelo/projeto_aplicado
```

O repositório é público e contém os artefatos da aplicação, documentação de segurança e workflow de implantação.

---

## 🔐 Configuração segura do GitHub

O acesso ao repositório a partir do ambiente de desenvolvimento utiliza autenticação por chave SSH.

A chave privada permanece armazenada localmente e não faz parte do repositório.

O repositório utiliza o remote SSH:

```text
git@github.com:romeritomelo/projeto_aplicado.git
```

A implantação automatizada utiliza uma chave SSH distinta, armazenada de forma segura nos GitHub Secrets para acesso à VM de produção.

---

## 🔒 Prevenção de vazamento de dados

O projeto possui um arquivo `.gitignore` para impedir o versionamento de arquivos que possam conter informações sensíveis ou arquivos temporários.

Entre os padrões ignorados estão:

* `.env`;
* `.env.*`;
* arquivos temporários;
* arquivos de backup;
* arquivos do sistema;
* configurações locais do VS Code.

As credenciais reais do banco de dados permanecem fora do repositório público.

Também não são armazenadas no repositório:

* chaves privadas SSH;
* credenciais da OCI;
* senhas hardcoded;
* credenciais reais do banco de dados;
* arquivos locais contendo informações sensíveis.

---

# 💻 Eixo 3: Desenvolvimento — Protótipo de Software Web

## 🛠️ Tecnologias utilizadas

A aplicação foi desenvolvida utilizando:

* PHP 8.4;
* Apache;
* MySQL HeatWave;
* HTML/CSS;
* JavaScript;
* Docker;
* Traefik.

O projeto utiliza uma organização baseada no padrão MVC.

---

## 🏗️ Arquitetura da aplicação

```text
projeto_aplicado/

├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── Services/
│   └── Views/
├── config/
├── css/
├── includes/
├── js/
├── storage/
├── .github/
│   └── workflows/
│       └── deploy.yml
├── dashboard.php
├── index.php
├── login.php
└── logout.php
```

A arquitetura separa responsabilidades entre controladores, modelos, serviços e visualizações.

---

# 🤖 Desenvolvimento assistido por Inteligência Artificial

O projeto foi desenvolvido com apoio de ferramentas de Inteligência Artificial, utilizadas como recurso auxiliar durante:

* análise;
* desenvolvimento;
* revisão de código;
* depuração;
* refatoração;
* implementação de mecanismos de segurança.

Foram utilizadas principalmente:

* Gemini;
* ChatGPT.

As ferramentas de IA foram utilizadas como apoio ao desenvolvimento seguro e à análise das implementações.

---

# 🔑 Requisitos funcionais

O protótipo possui a estrutura mínima exigida para autenticação:

### Login

A aplicação possui uma tela de login com autenticação por e-mail e senha.

### Página interna

Após autenticação, o usuário possui acesso ao Dashboard, que é protegido no lado do servidor.

Usuários não autenticados são redirecionados para a página de login.

### Logout

A aplicação possui botão de logout funcional.

O logout destrói a sessão e invalida o cookie correspondente.

---

# 🛡️ Segurança da aplicação

Foram implementadas diversas medidas de segurança:

* autenticação de usuários;
* proteção contra CSRF;
* controle de tentativas de autenticação;
* `password_hash()`;
* `password_verify()`;
* regeneração do ID de sessão;
* encerramento seguro da sessão;
* proteção dos cookies;
* HSTS;
* Content Security Policy;
* `X-Content-Type-Options`;
* `X-Frame-Options`;
* `Referrer-Policy`;
* `Permissions-Policy`;
* controle de expiração das sessões;
* prepared statements;
* codificação de saída HTML;
* validação de dados.

---

# 🛡️ OWASP Top 10:2025 — Mitigações implementadas

O código-fonte implementa mecanismos de mitigação para mais de três categorias do OWASP Top 10:2025, superando o mínimo de três categorias exigido no escopo.

Foram documentadas:

* **A01 — Broken Access Control**
* **A02 — Security Misconfiguration**
* **A04 — Cryptographic Failures**
* **A05 — Injection**
* **A07 — Authentication Failures**

---

## A01 — Broken Access Control

A aplicação implementa controles de acesso no lado do servidor para impedir que usuários não autenticados acessem recursos protegidos.

O acesso ao Dashboard depende da existência de:

```php
$_SESSION['usuario_id']
```

Usuários não autenticados são redirecionados para:

```text
/login.php
```

### Principais medidas

* verificação da sessão;
* proteção das páginas autenticadas;
* controle da sessão no servidor;
* encerramento da sessão no logout.

### Local da implementação

```text
dashboard.php
app/Controllers/DashboardController.php
includes/auth.php
config/security.php
logout.php
```

---

## A02 — Security Misconfiguration

Foram adotadas medidas para reduzir configurações inseguras na aplicação e na infraestrutura.

### Principais medidas

* HTTPS;
* redirecionamento HTTP → HTTPS;
* HSTS;
* CSP;
* `X-Content-Type-Options`;
* `X-Frame-Options`;
* `Referrer-Policy`;
* `Permissions-Policy`;
* cookies seguros;
* controle de expiração das sessões;
* Firewalld;
* Fail2Ban;
* SSH por chave;
* autenticação SSH por senha desabilitada;
* credenciais do banco fora do repositório;
* `.gitignore`.

### Local da implementação

```text
config/security.php
.gitignore
Traefik
Firewalld
Fail2Ban
SSH
```

---

## A04 — Cryptographic Failures

A aplicação utiliza HTTPS para proteger a comunicação entre cliente e servidor.

O Traefik gerencia os certificados Let's Encrypt.

As senhas dos usuários não são armazenadas em texto puro.

A autenticação utiliza:

```php
password_hash()
password_verify()
```

Os cookies de sessão utilizam:

```text
Secure
HttpOnly
SameSite
```

### Local da implementação

```text
app/Controllers/AuthController.php
config/security.php
config/database.php
Traefik
HTTPS/Let's Encrypt
```

---

## A05 — Injection

A aplicação utiliza **PDO Prepared Statements** com parâmetros vinculados nas consultas SQL.

Exemplo:

```php
$stmt = $this->pdo->prepare($sql);
$stmt->execute([
    'email' => $email
]);
```

Não são utilizados dados fornecidos pelo usuário concatenados diretamente às consultas SQL.

Também são utilizados mecanismos de codificação de saída HTML com:

```php
htmlspecialchars()
```

e Content Security Policy como camada adicional de proteção.

### Principais medidas

* PDO Prepared Statements;
* parâmetros vinculados;
* ausência de concatenação direta de dados do usuário em SQL;
* validação dos dados;
* `htmlspecialchars()`;
* CSP.

### Local da implementação

```text
app/Models/User.php
app/Models/LoginAttempt.php
login.php
config/security.php
```

---

## A07 — Authentication Failures

A aplicação implementa mecanismos de proteção relacionados à autenticação e ao gerenciamento de sessões.

### Principais medidas

* autenticação por e-mail e senha;
* validação dos dados;
* `password_verify()`;
* registro das tentativas;
* limitação por conta;
* limitação por IP;
* atraso após falha;
* regeneração do ID da sessão;
* controle do tempo da sessão;
* logout com destruição da sessão.

### Limites de autenticação

O `AuthController` utiliza:

```text
5 tentativas inválidas por conta
20 tentativas inválidas por IP
Janela: 10 minutos
```

Após autenticação válida, é utilizado:

```php
session_regenerate_id(true)
```

reduzindo o risco de session fixation.

### Local da implementação

```text
app/Controllers/AuthController.php
app/Models/User.php
app/Models/LoginAttempt.php
config/security.php
login.php
logout.php
dashboard.php
```

---

# 🔄 Eixos 1, 2 e 3 — Integração e Entrega Contínuas

## GitHub Actions

O projeto utiliza GitHub Actions para automatizar a implantação da aplicação no ambiente de produção.

O workflow está localizado em:

```text
.github/workflows/deploy.yml
```

O pipeline possui foco em **Continuous Deployment (CD)** e é executado automaticamente quando ocorre um `push` na branch `main`.

Também pode ser executado manualmente por meio de:

```text
workflow_dispatch
```

---

## Fluxo de implantação

```text
┌───────────────────────┐
│ Computador local   │
│                       │
│ Desenvolvimento       │
│ Commit + Push         │
└──────────┬────────────┘
           │
           ▼
┌───────────────────────┐
│ GitHub                │
│ Repositório público   │
└──────────┬────────────┘
           │
           │ Push na main
           ▼
┌───────────────────────┐
│ GitHub Actions        │
│                       │
│ Checkout              │
│ SSH                   │
│ rsync                 │
│ Smoke test            │
└──────────┬────────────┘
           │
           ▼
┌───────────────────────┐
│ VM1 — OCI             │
│                       │
│ Traefik               │
│ Apache/PHP             │
│ Aplicação             │
└───────────────────────┘
```

---

## 🔐 Gerenciamento seguro das credenciais

As credenciais utilizadas pelo pipeline não são armazenadas no código-fonte.

O workflow utiliza GitHub Secrets:

```text
PROD_SSH_KEY
PROD_KNOWN_HOSTS
PROD_HOST
PROD_USER
```

A chave privada utilizada no deploy permanece protegida pelo mecanismo de Secrets do GitHub.

Também é utilizado:

```text
StrictHostKeyChecking=yes
```

para impedir que o workflow simplesmente desabilite a verificação da identidade do servidor SSH.

---

## 🚀 Etapas do workflow

### 1. Checkout

O GitHub Actions utiliza:

```text
actions/checkout@v4
```

para obter o código do repositório.

### 2. Configuração SSH

A chave privada de produção é obtida por meio do Secret:

```text
PROD_SSH_KEY
```

O arquivo `known_hosts` é configurado a partir de:

```text
PROD_KNOWN_HOSTS
```

### 3. Teste da conexão

O workflow testa a conexão SSH com a VM1 antes de iniciar a implantação.

### 4. Sincronização

A aplicação é sincronizada para:

```text
/home/opc/html/
```

utilizando `rsync`.

São excluídos:

```text
.git/
.github/
.env
.env.*
*_bkp.*
*_backup.*
```

O parâmetro:

```text
--delete
```

mantém o diretório de publicação sincronizado com o conteúdo versionado.

As credenciais reais do banco continuam fora do diretório publicado:

```text
/home/opc/database.php
```

### 5. Validação pós-deploy

Após a implantação, o workflow realiza uma requisição HTTPS para:

```text
https://romeritomelo.seg.br/login.php
```

utilizando:

```text
curl --fail
```

Essa etapa funciona como um **smoke test**, verificando se a aplicação está respondendo corretamente após o deploy.

---

# 🔐 Segurança do pipeline

O pipeline possui as seguintes características:

* chaves privadas fora do código;
* utilização de GitHub Secrets;
* `known_hosts`;
* `StrictHostKeyChecking=yes`;
* SSH para acesso à VM;
* rsync para sincronização;
* exclusão de arquivos sensíveis;
* validação pós-deploy;
* execução automática após `push` na branch `main`.

A chave SSH utilizada pelo computador local para acessar o GitHub é **distinta** da chave utilizada pelo GitHub Actions para acessar a VM de produção.

---

# 📋 Checklist Final de Entrega

## ☁️ Eixo 1 — Infraestrutura

* [x] Aplicação web hospedada em ambiente de nuvem.
* [x] Oracle Cloud Infrastructure utilizada com recursos do Free Tier.
* [x] Oracle Linux 9.8 utilizado na VM de produção, conforme autorização acadêmica.
* [x] Apache configurado como Web Server.
* [x] Aplicação acessível publicamente pela Internet.
* [x] Acesso administrativo utilizando chave SSH.
* [x] Autenticação SSH por senha desabilitada.
* [x] Firewalld configurado.
* [x] Porta SSH protegida pelo Fail2Ban.
* [x] Fail2Ban configurado para 4 erros e banimento de 24 horas.
* [x] HTTPS configurado.
* [x] Certificado Let's Encrypt configurado e gerenciado pelo Traefik.
* [x] Redirecionamento HTTP → HTTPS configurado.
* [x] TLS 1.2 e TLS 1.3 disponíveis.
* [x] Protocolos TLS obsoletos desabilitados.
* [x] HTTP/2 configurado.
* [x] HTTP/3/QUIC configurado.
* [x] HSTS configurado.
* [x] Suporte a PQC identificado.
* [x] Qualys SSL Labs: A+.

## 📦 Eixo 2 — Repositório

* [x] Repositório público no GitHub.
* [x] Código versionado com Git.
* [x] Remote do ambiente de desenvolvimento configurado via SSH.
* [x] Chave SSH pessoal configurada para acesso ao GitHub.
* [x] Chave privada mantida fora do repositório.
* [x] `.gitignore` configurado.
* [x] Credenciais do banco fora do código público.
* [x] Chaves privadas não armazenadas no repositório.
* [x] Credenciais da infraestrutura não armazenadas no código.
* [x] GitHub Secrets utilizados pelo pipeline.

## 💻 Eixo 3 — Desenvolvimento

* [x] Aplicação Web desenvolvida.
* [x] Tela de Login.
* [x] Página interna protegida por autenticação.
* [x] Logout funcional.
* [x] Desenvolvimento assistido por Inteligência Artificial.
* [x] Gemini utilizado como ferramenta de IA.
* [x] ChatGPT utilizado como ferramenta de IA.
* [x] Proteções contra CSRF.
* [x] Controle de tentativas de autenticação.
* [x] Proteção de sessão.
* [x] Prepared Statements.
* [x] Codificação de saída HTML.
* [x] Cabeçalhos HTTP de segurança.
* [x] Mitigação documentada de A01.
* [x] Mitigação documentada de A02.
* [x] Mitigação documentada de A04.
* [x] Mitigação documentada de A05.
* [x] Mitigação documentada de A07.

O requisito mínimo de três categorias do OWASP Top 10:2025 é superado, com cinco categorias documentadas.

## 🔄 Integração e Entrega Contínuas

* [x] GitHub Actions configurado.
* [x] Workflow `.github/workflows/deploy.yml`.
* [x] Execução automática após `push` na branch `main`.
* [x] Configuração manual por `workflow_dispatch`.
* [x] SSH utilizado no deploy.
* [x] Credenciais armazenadas em GitHub Secrets.
* [x] `known_hosts` configurado.
* [x] `StrictHostKeyChecking=yes`.
* [x] Sincronização por rsync.
* [x] Exclusão de arquivos sensíveis.
* [x] Validação pós-deploy.
* [x] Aplicação publicada automaticamente na VM de produção.

---

# 📊 Status do Projeto

| Item                  | Status                    |
| --------------------- | ------------------------- |
| Aplicação Web         | Concluída                 |
| Login                 | Concluído                 |
| Dashboard             | Concluído                 |
| Logout                | Concluído                 |
| HTTPS                 | Configurado               |
| Let's Encrypt         | Configurado               |
| HTTP/2                | Configurado               |
| HTTP/3/QUIC           | Configurado               |
| HSTS                  | Configurado               |
| SSH por chave         | Configurado               |
| Fail2Ban              | Configurado               |
| `.gitignore`          | Configurado               |
| GitHub público        | Configurado               |
| GitHub via SSH        | Configurado               |
| GitHub Actions        | Configurado               |
| Continuous Deployment | Configurado               |
| Validação pós-deploy  | Configurada               |
| Qualys SSL Labs       | A+                        |
| PQC                   | Suportado                 |
| OWASP Top 10          | 5 categorias documentadas |

---

# 📁 Estrutura resumida do projeto

```text
projeto_aplicado/
│
├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── Services/
│   └── Views/
│
├── config/
│
├── css/
├── includes/
├── js/
├── storage/
│
├── .github/
│   └── workflows/
│       └── deploy.yml
│
├── dashboard.php
├── index.php
├── login.php
├── logout.php
├── README.md
└── .gitignore
```

---

# 🔗 Repositório

O código-fonte do projeto está disponível publicamente no GitHub:

https://github.com/romeritomelo/projeto_aplicado

---

# 🤖 Desenvolvimento com Inteligência Artificial

O projeto foi desenvolvido com apoio de ferramentas de Inteligência Artificial, principalmente **Gemini e ChatGPT**, utilizadas como recurso auxiliar durante as etapas de análise, desenvolvimento, revisão, depuração, refatoração e implementação de mecanismos de segurança.

As ferramentas de IA foram utilizadas como apoio ao desenvolvimento, enquanto a configuração da infraestrutura, operação do ambiente, validação das medidas de segurança e implantação foram realizadas no ambiente do projeto.
