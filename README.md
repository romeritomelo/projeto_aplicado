# Finanças+

Aplicação web desenvolvida como Projeto Aplicado da Pós-Graduação EAD em Segurança da Informação e Análise Forense da UNCISAL.

O projeto consiste em uma aplicação web de controle financeiro com autenticação de usuários e recursos de segurança implementados tanto na aplicação quanto na infraestrutura de hospedagem.

## Objetivo

O projeto tem como objetivo demonstrar a aplicação prática de conceitos de desenvolvimento seguro, segurança de aplicações web, proteção da infraestrutura e implantação automatizada em ambiente de nuvem.

A aplicação está hospedada na Oracle Cloud Infrastructure (OCI) e utiliza HTTPS com certificado digital emitido pelo Let's Encrypt.

## Tecnologias utilizadas

* PHP 8.4
* Apache
* MySQL HeatWave
* Docker
* Traefik
* Let's Encrypt
* Oracle Cloud Infrastructure (OCI)
* Oracle Linux 9.8
* Firewalld
* Fail2Ban
* Git
* GitHub
* GitHub Actions
* SSH
* rsync

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
├── .github/
│   └── workflows/
│       └── deploy.yml
├── dashboard.php
├── index.php
├── login.php
└── logout.php
```

As credenciais de acesso ao banco de dados não ficam armazenadas no repositório público. O arquivo `config/database.php` contém somente uma referência para o arquivo real de configuração localizado fora da raiz do projeto:

```text
/home/opc/database.php
```

Dessa forma, informações sensíveis de conexão com o banco de dados não são disponibilizadas no código público.

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
* Content Security Policy (CSP);
* firewall;
* Fail2Ban;
* acesso SSH utilizando autenticação por chave;
* desabilitação da autenticação SSH por senha;
* separação das credenciais do banco de dados do código público;
* utilização de `.gitignore`;
* TLS 1.3 com suporte a mecanismos de troca de chaves pós-quânticos (PQC), quando suportados pelo cliente;
* implantação automatizada por GitHub Actions.

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

No `AuthController`, são aplicados limites de cinco tentativas inválidas para uma mesma conta e vinte tentativas para um mesmo endereço IP dentro de uma janela de dez minutos.

Após uma autenticação válida, o identificador da sessão é regenerado por meio de `session_regenerate_id(true)`, reduzindo o risco de session fixation.

### A04 — Cryptographic Failures

A aplicação utiliza HTTPS para proteger a comunicação entre o cliente e o servidor.

O HTTPS é terminado no Traefik, que utiliza certificados digitais emitidos pelo Let's Encrypt. O ambiente também realiza o redirecionamento das requisições HTTP para HTTPS.

Além da proteção da comunicação, as credenciais de usuários não são armazenadas em texto puro. A autenticação utiliza as funções nativas `password_hash()` e `password_verify()` do PHP.

Também são utilizados mecanismos de proteção do cookie de sessão, incluindo `Secure`, `HttpOnly` e `SameSite`, reduzindo a exposição do identificador de sessão no navegador.

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
* `X-Content-Type-Options`;
* `X-Frame-Options`;
* `Referrer-Policy`;
* `Permissions-Policy`;
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
* Docker 29.7.2;
* Traefik 3.7.11 como proxy reverso;
* HTTP/3 sobre QUIC;
* Apache/PHP para execução da aplicação;
* MySQL HeatWave para persistência dos dados;
* MySQL HeatWave protegido em subnet privada na VCN da OCI;
* Firewalld para controle do tráfego;
* Fail2Ban para proteção contra tentativas de acesso SSH;
* certificado Let's Encrypt para HTTPS.

O acesso administrativo à máquina virtual utiliza autenticação por chave SSH.

## TLS, HTTPS e HSTS

A aplicação utiliza HTTPS com certificado digital emitido pelo Let's Encrypt e gerenciamento automatizado do certificado pelo Traefik.

A configuração TLS disponibiliza TLS 1.2 e TLS 1.3, mantendo desabilitados protocolos obsoletos como TLS 1.0, TLS 1.1, SSL 2 e SSL 3.

Também são utilizados mecanismos de Forward Secrecy.

### HSTS

A aplicação implementa HTTP Strict Transport Security (HSTS) por meio de `config/security.php`.

A política utilizada é:

```http
Strict-Transport-Security: max-age=31536000; includeSubDomains
```

Isso determina que o navegador deve utilizar HTTPS durante o período definido de um ano e aplica a política também aos subdomínios.

### HTTP/2 e HTTP/3

O ambiente suporta HTTP/2 e anuncia suporte a HTTP/3 por meio do protocolo QUIC.

O HTTPS é terminado no Traefik, que atua como proxy reverso para a aplicação Apache/PHP.

### SNI

O ambiente utiliza Server Name Indication (SNI) para permitir a seleção do certificado correspondente ao domínio durante o handshake TLS.

Clientes modernos com suporte a SNI recebem o certificado correspondente ao domínio da aplicação.

### Criptografia pós-quântica

A avaliação externa identificou suporte a mecanismos híbridos de troca de chaves pós-quânticos (PQC), incluindo `X25519MLKEM768`, quando suportados pelo cliente.

## Validação externa da configuração TLS

A configuração HTTPS/TLS foi submetida ao **Qualys SSL Labs SSL Server Test**.

**Resultado obtido: A+**

A avaliação identificou, entre outros aspectos:

* certificado confiável emitido pelo Let's Encrypt;
* certificado RSA de 4096 bits;
* TLS 1.2 e TLS 1.3;
* Forward Secrecy;
* HSTS com longa duração;
* suporte a SNI;
* HTTP/2;
* suporte a HTTP/3;
* suporte a mecanismos de troca de chaves pós-quânticos;
* ausência de vulnerabilidades TLS históricas relevantes identificadas pelo teste.

O resultado A+ representa a avaliação da configuração TLS no momento do teste e pode variar após alterações na infraestrutura, certificados ou políticas de segurança.

## Controle de sessão

A aplicação possui mecanismos para limitar o tempo de vida das sessões.

São utilizados:

* tempo máximo de inatividade;
* tempo máximo absoluto de sessão;
* regeneração do ID após autenticação;
* invalidação do cookie no logout;
* destruição da sessão no servidor.

Essas medidas reduzem riscos relacionados ao sequestro e reutilização indevida de sessões.

## CI/CD — GitHub Actions

O projeto utiliza GitHub Actions para automatizar a implantação da aplicação no ambiente de produção.

O workflow está localizado em:

```text
.github/workflows/deploy.yml
```

O pipeline possui foco em **Continuous Deployment (CD)** e é executado automaticamente quando ocorre um `push` na branch `main`. Também pode ser executado manualmente por meio de `workflow_dispatch`.

### Fluxo de implantação

```text
Desenvolvimento
      │
      ▼
 git push main
      │
      ▼
    GitHub
      │
      ▼
GitHub Actions
      │
      ├── Checkout do código
      │
      ├── Configuração segura do SSH
      │
      ├── Teste da conexão SSH
      │
      ├── Sincronização via rsync
      │
      └── Validação pós-deploy
               │
               ▼
          VM1 — OCI
               │
               ▼
       Aplicação em produção
```

### Etapas do workflow

**1. Checkout**

O GitHub Actions utiliza `actions/checkout` para obter o código do repositório no runner.

**2. Configuração do SSH**

A chave privada utilizada no processo de implantação não é armazenada no código-fonte. Ela é disponibilizada ao workflow por meio do GitHub Secret:

```text
PROD_SSH_KEY
```

Também são utilizados os seguintes Secrets:

```text
PROD_KNOWN_HOSTS
PROD_HOST
PROD_USER
```

O arquivo `known_hosts` é configurado e o SSH utiliza:

```text
StrictHostKeyChecking=yes
```

Dessa forma, o workflow não desabilita a verificação da identidade do servidor SSH.

**3. Teste de conectividade**

Antes da implantação, o workflow estabelece uma conexão SSH com a VM1 e verifica se a conexão está funcionando.

**4. Sincronização da aplicação**

A aplicação é sincronizada para:

```text
/home/opc/html/
```

na VM1 utilizando `rsync`.

São excluídos do processo de sincronização:

```text
.git/
.github/
.env
.env.*
*_bkp.*
*_backup.*
```

O uso de `--delete` garante que arquivos removidos do repositório também sejam removidos do diretório de publicação da aplicação na VM1.

As credenciais reais do banco de dados permanecem fora do diretório sincronizado, em:

```text
/home/opc/database.php
```

**5. Validação pós-deploy**

Após a sincronização, o workflow executa uma requisição HTTPS para:

```text
https://romeritomelo.seg.br/login.php
```

A resposta é validada utilizando `curl --fail`.

Essa etapa funciona como um **smoke test**, verificando se a aplicação publicada está respondendo através de HTTPS após o deploy.

### Características de segurança do pipeline

O pipeline evita o armazenamento de credenciais diretamente no código-fonte e utiliza GitHub Secrets para os dados necessários à conexão com a VM de produção.

A chave SSH privada e as informações de acesso não fazem parte do repositório público.

## Status do projeto

* Aplicação web: concluída
* Autenticação: concluída
* Dashboard: concluído
* Logout: concluído
* HTTPS: configurado
* Let's Encrypt: configurado
* HTTP/2: configurado
* HTTP/3/QUIC: configurado
* HSTS: configurado
* SSH por chave: configurado
* Fail2Ban: configurado
* `.gitignore`: configurado
* Repositório público: configurado
* Documentação de segurança: em conclusão
* GitHub Actions: configurado
* Continuous Deployment (CD): configurado
* Validação pós-deploy: configurada
* Avaliação Qualys SSL Labs: A+

## Repositório

O código-fonte do projeto está disponível publicamente no GitHub:

https://github.com/romeritomelo/projeto_aplicado

## Desenvolvimento

O projeto foi desenvolvido com apoio de ferramentas de Inteligência Artificial (Gemini e ChatGPT), utilizadas como recurso auxiliar durante as etapas de análise, desenvolvimento, revisão e implementação de mecanismos de segurança.
