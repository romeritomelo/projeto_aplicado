# Finanças+

Aplicação web desenvolvida como Projeto Aplicado da Pós-Graduação EAD em Segurança da Informação e Análise Forense da UNCISAL.

O projeto consiste em uma aplicação web de controle financeiro com autenticação de usuários e recursos de segurança implementados tanto na aplicação quanto na infraestrutura de hospedagem.

## Objetivo

O projeto tem como objetivo demonstrar a aplicação prática de conceitos de desenvolvimento seguro, segurança de aplicações web e proteção da infraestrutura em ambiente de nuvem.

A aplicação está hospedada na Oracle Cloud Infrastructure (OCI) e utiliza HTTPS com certificado Let's Encrypt.

## Tecnologias utilizadas

* PHP 8.4
* Apache
* MySQL HeatWave
* Docker
* Traefik
* Let's Encrypt
* Oracle Cloud Infrastructure (OCI)
* Firewalld
* Fail2Ban
* GitHub
* GitHub Actions

## Arquitetura da aplicação

A aplicação utiliza uma organização baseada no padrão MVC, separando responsabilidades entre controladores, modelos, serviços e visualizações.

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
├── dashboard.php
├── index.php
├── login.php
└── logout.php
```

As credenciais de acesso ao banco de dados não ficam armazenadas no repositório público. O arquivo `config/database.php` contém somente uma referência para o arquivo real de configuração localizado fora da raiz do projeto:

`/home/opc/database.php`

Dessa forma, informações sensíveis não são disponibilizadas no código público.

## Segurança

Foram adotadas diversas medidas de segurança na aplicação e na infraestrutura. Entre elas:

* autenticação de usuários;
* proteção contra CSRF;
* controle de tentativas de autenticação;
* armazenamento de senhas utilizando funções de hash do PHP;
* regeneração do identificador de sessão após autenticação;
* encerramento seguro da sessão;
* proteção dos cookies de sessão;
* cabeçalhos HTTP de segurança;
* HTTPS;
* HSTS;
* firewall;
* Fail2Ban;
* acesso SSH utilizando autenticação por chave;
* separação das credenciais do banco de dados do código público.

## OWASP Top 10 — Mitigações implementadas

### A07 — Authentication Failures

A aplicação implementa mecanismos para reduzir riscos relacionados à autenticação e ao gerenciamento de sessões.

**Principais medidas implementadas:**

* autenticação por e-mail e senha;
* validação dos dados recebidos no formulário;
* verificação das credenciais utilizando `password_verify()`;
* registro das tentativas de autenticação;
* limitação de tentativas inválidas por conta;
* limitação de tentativas inválidas por endereço IP;
* atraso após tentativa de autenticação inválida;
* regeneração do ID da sessão após autenticação;
* controle de tempo da sessão;
* logout com destruição da sessão.

**Local da implementação:**

* `app/Controllers/AuthController.php`
* `app/Models/User.php`
* `app/Models/LoginAttempt.php`
* `config/security.php`
* `login.php`
* `logout.php`
* `dashboard.php`

No `AuthController`, por exemplo, são aplicados limites de cinco tentativas inválidas para uma mesma conta e vinte tentativas para um mesmo endereço IP dentro de uma janela de dez minutos.

Após uma autenticação válida, o identificador da sessão é regenerado por meio de `session_regenerate_id(true)`, reduzindo o risco de session fixation.

### A04 — Cryptographic Failures

A aplicação utiliza HTTPS para proteger a comunicação entre o cliente e o servidor.

O HTTPS é terminado no Traefik, que utiliza certificados digitais emitidos pelo Let's Encrypt. O ambiente também realiza o redirecionamento das requisições HTTP para HTTPS.

Além da proteção da comunicação, as credenciais de usuários não são armazenadas em texto puro. A autenticação utiliza as funções nativas `password_hash()` e `password_verify()` do PHP.

Também são utilizados mecanismos de proteção do cookie de sessão, reduzindo a exposição do identificador de sessão no navegador.

**Local da implementação:**

* `app/Controllers/AuthController.php`
* `config/security.php`
* configuração do Traefik
* configuração do HTTPS/Let's Encrypt

As credenciais do banco de dados são mantidas fora do repositório público, sendo referenciadas pelo arquivo `config/database.php`.

### A02 — Security Misconfiguration

Foram adotadas medidas para reduzir configurações inseguras tanto na aplicação quanto no servidor.

Entre as medidas implementadas estão:

* utilização de HTTPS;
* redirecionamento HTTP para HTTPS;
* HSTS;
* Content Security Policy (CSP);
* proteção dos cookies de sessão;
* controle de expiração das sessões;
* Firewalld ativo na VM;
* Fail2Ban monitorando o serviço SSH;
* autenticação SSH por chave;
* desabilitação da autenticação SSH por senha;
* separação das credenciais do banco de dados do código público;
* utilização de `.gitignore` para impedir o versionamento de arquivos sensíveis e temporários.

**Local da implementação:**

* `config/security.php`
* `.gitignore`
* configuração do Traefik
* configuração do Firewalld
* configuração do Fail2Ban
* configuração do SSH

No ambiente de produção, o Fail2Ban monitora o serviço SSH e bloqueia endereços IP após sucessivas tentativas de autenticação malsucedidas.

## Proteção do repositório

O projeto possui um `.gitignore` para impedir o versionamento de arquivos que possam conter informações sensíveis ou arquivos temporários.

Entre os arquivos e padrões ignorados estão:

* `.env`
* `.env.*`
* arquivos temporários;
* arquivos de backup;
* arquivos do sistema;
* configurações locais do VS Code.

As credenciais reais do banco de dados permanecem fora do repositório público.

## Infraestrutura

A aplicação está hospedada em uma máquina virtual na Oracle Cloud Infrastructure.

A arquitetura utiliza:

* Oracle Linux 9.8;
* Docker;
* Traefik como proxy reverso;
* HTTP3/QUIC implementado;
* Apache/PHP para execução da aplicação;
* MySQL HeatWave para persistência dos dados;
* MYSQL HeatWare separado em Subnet privada;
* Firewalld para controle do tráfego;
* Fail2Ban para proteção contra tentativas de acesso SSH;
* Certificado Let's Encrypt para HTTPS.

O acesso administrativo à máquina virtual utiliza autenticação por chave SSH.

## Controle de sessão

A aplicação possui mecanismos para limitar o tempo de vida das sessões.

São utilizados:

* tempo máximo de inatividade;
* tempo máximo absoluto de sessão;
* regeneração do ID após autenticação;
* invalidação do cookie no logout;
* destruição da sessão no servidor.

Essas medidas reduzem riscos relacionados ao sequestro e reutilização indevida de sessões.

## Repositório

O código-fonte do projeto está disponível publicamente no GitHub:

https://github.com/romeritomelo/projeto_aplicado

## Status do projeto

* Aplicação web: concluída
* Autenticação: concluída
* Dashboard: concluído
* Logout: concluído
* HTTPS: configurado
* Let's Encrypt: configurado
* SSH por chave: configurado
* Fail2Ban: configurado
* `.gitignore`: configurado
* Repositório público: configurado
* Documentação de segurança: em conclusão
* CI/CD com GitHub Actions: em implementação

## Desenvolvimento

O projeto foi desenvolvido com apoio de ferramentas de Inteligência Artificial (Gemini e ChatGpt), utilizadas como recurso auxiliar durante as etapas de análise, desenvolvimento, revisão e implementação de mecanismos de segurança.
