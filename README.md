# Rail Brazil
> "Visualização das ferrovias brasileiras, desenvolvida como projeto de Graduação com framework Laravel e Banco de Dados em Nuvem."

![Status do Projeto](https://img.shields.io/badge/Status-Andamento-orange) ![Laravel](https://img.shields.io/badge/Laravel-2e2e2e?logo=laravel) ![Supabase](https://img.shields.io/badge/Database-Supabase-3ECF8E)

---

## Funcionalidades

- **Roteamento Inteligente:** Cálculo da rota utilizando a Teoria dos Grafos diretamente na base de dados (pgRouting).
- **Cálculo Tarifário Dinâmico:** Aplicação automática do teto tarifário escalonado da ANTT (Parcelas Fixas e Variáveis) conforme a distância e o tipo de mercadoria.
- **Métricas de Sustentabilidade (ESG):** Comparação e cálculo exato da economia de emissões de CO₂ do percurso ferroviário em relação ao modal rodoviário.
- **Importação de Malhas (GeoJSON):** Renderização instantânea de traçados e expansões ferroviárias planejadas através da leitura de ficheiros `.geojson` via *FileReader API*.
- **Gestão de Histórico (Dashboard):** Área autenticada que permite aos usuários armazenar, visualizar e excluir cenários simulados de rotas.

## Tecnologias Utilizadas

- **Backend:** PHP 8, Laravel 12
- **Frontend:** Blade Templates, Tailwind CSS, JavaScript
- **Mapas & Geoprocessamento:** Leaflet.js
- **Banco de Dados:** PostgreSQL hospedado no Supabase (com extensões PostGIS e pgRouting)
- **Autenticação:** Laravel Breeze

## Como Rodar o Projeto

Siga os passos abaixo para rodar o projeto na sua máquina.

### Pré-requisitos
Antes de começar, certifique-se de que tem as seguintes ferramentas instaladas:
- [PHP](https://www.php.net/downloads) (versão 8.2 ou superior)
- [Composer](https://getcomposer.org/)
- [Node.js e NPM](https://nodejs.org/)
- Uma conta no [Supabase](https://supabase.com/) (com um banco de dados PostgreSQL configurado e as extensões PostGIS ativas).

### Passo a passo da Instalação

### **Passo 1 - Clone o repositório**
```bash
git clone https://github.com/CJSabino/rail-brazil.git
cd rail-brazil
```

### **Passo 2 - Instale as Dependências**
```bash
composer install
npm install
```

### **Passo 3 - Configure o Ambiente**
```bash
cp .env.example .env
php artisan key:generate
```
Abra o arquivo .env criado e configure as credenciais do seu banco de dados Supabase na seção de Database

### **Passo 4 - Banco de Dados**
```bash
php artisan migrate
```

### **Passo 5 - Compilar e Iniciar o Servidor**
```bash
npm run build
php artisan serve
```

---

Este projeto foi desenvolvido para fins académicos e de pesquisa.
