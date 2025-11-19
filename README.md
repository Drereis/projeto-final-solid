# 🅿️ Projeto Final SOLID - Controle de Estacionamento

Este é um projeto final desenvolvido em PHP aplicando os princípios SOLID (Single Responsibility, Open-Closed, Liskov Substitution, Interface Segregation, Dependency Inversion) e boas práticas de clean code e design patterns.

## 📋 Descrição

O sistema de controle de estacionamento inteligente permite gerenciar a entrada e saída de veículos, calcular preços baseados no tipo de veículo e tempo de permanência, e gerar relatórios de faturamento. O projeto demonstra a aplicação prática de arquitetura limpa e princípios SOLID em um contexto real.

## ✨ Funcionalidades

- **🚗 Check-in de Veículos**: Registro de entrada com validação de placa (padrão Mercosul) e tipo de veículo
- **🚪 Check-out de Veículos**: Cálculo automático do tempo e custo baseado no tipo de veículo
- **✅ Validação de Dados**: Verificação de formato de placa e tipos de veículo permitidos
- **📊 Relatórios de Faturamento**: Visualização de totais por tipo de veículo e estatísticas gerais
- **🌐 Interface Web**: Interface responsiva construída com HTML, CSS (Tailwind) e PHP
- **💾 Persistência de Dados**: Suporte a SQLite e migração de dados TXT para SQLite

## 🚗 Tipos de Veículo e Preços

- **🚗 Carro**: R$ 5,00 por hora
- **🏍️ Moto**: R$ 3,00 por hora
- **🚚 Caminhão**: R$ 10,00 por hora

## 🏗️ Arquitetura

O projeto segue uma arquitetura em camadas inspirada no Domain-Driven Design:

- **🏛️ Domain**: Contém as regras de negócio, entidades, serviços de domínio e interfaces
  - Model: `Vehicle`, `ParkingRecord`
  - Service: Estratégias de precificação (`CarPricing`, `MotorcyclePricing`, `TruckPricing`, `PricingService` `PricingStrategy`)
  - Repository: Interface `ParkingRecordRepository`
  - Validator: `VehicleValidator`

- **⚙️ Application**: Camada de aplicação com serviços que orquestram as operações
  - Service: `ParkingControlService`

- **🔧 Infrastructure**: Implementações concretas das interfaces
  - Repository: `SqliteParkingRecordRepository`, `TxtParkingRecordRepository`

## 🛠️ Tecnologias Utilizadas

- **🐘 PHP 8.0+** com strict types
- **🗄️ SQLite** para persistência de dados
- **📦 Composer** para gerenciamento de dependências
- **🎨 Tailwind CSS** para estilização da interface
- **🔍 PHP CodeSniffer** para análise de código (dev)

## 📋 Requisitos

- 🐘 PHP 8.0 ou superior
- 📦 Composer
- 🌐 Servidor web (Apache/Nginx) ou PHP built-in server
- 🗄️ SQLite3

## 🚀 Instalação

1. 📥 Clone o repositório:
   ```bash
   git clone https://github.com/Drereis/projeto-final-solid.git
   cd projeto-final-solid
   ```

2. 📦 Instale as dependências:
   ```bash
   composer install
   ```

3. 🔄 Execute a migração (se houver dados em TXT):
   ```bash
   php migrate.php
   ```

4. 🌐 Acesse no navegador: `http://localhost/projeto-final-solid`

## 📖 Uso

### 🌐 Interface Web

- **🏠 Página Principal** (`index.php`): Realize check-in e check-out de veículos, visualize relatório resumido
- **📊 Relatórios** (`report.php`): Visualize relatórios detalhados de faturamento

### ⚙️ Operações

1. **🚗 Check-in**:
   - Informe a placa do veículo (formato Mercosul: ABC1D34)
   - Selecione o tipo: carro, moto ou caminhao

2. **🚪 Check-out**:
   - Informe apenas a placa do veículo
   - O sistema calcula automaticamente o tempo e custo

## 📁 Estrutura do Projeto

```
projeto-final-solid/
├── composer.json
├── composer.lock
├── migrate.php
├── README.md
├── public/
│   ├── index.php
│   └── report.php
├── src/
│   ├── Application/
│   │   └── Service/
│   │       └── ParkingControlService.php
│   ├── Domain/
│   │   ├── Model/
│   │   │   ├── ParkingRecord.php
│   │   │   └── Vehicle.php
│   │   ├── Repository/
│   │   │   └── ParkingRecordRepository.php
│   │   ├── Service/
│   │   │   ├── CarPricing.php
│   │   │   ├── MotorcyclePricing.php
│   │   │   ├── PricingService.php
│   │   │   ├── PricingStrategy.php
│   │   │   └── TruckPricing.php
│   │   └── Validator/
│   │       └── VehicleValidator.php
│   └── Infra/
│       ├── SqliteParkingRecordRepository.php
│       └── TxtParkingRecordRepository.php
└── storage/
    ├── parking_records.db
    └── parking_records.txt
```

## 🎯 Princípios SOLID Aplicados

- **🔸 S (Single Responsibility)**: Cada classe tem uma única responsabilidade
- **🔸 O (Open-Closed)**: Extensível através de estratégias de precificação
- **🔸 L (Liskov Substitution)**: Implementações de repository são intercambiáveis
- **🔸 I (Interface Segregation)**: Interfaces específicas e coesas
- **🔸 D (Dependency Inversion)**: Dependências injetadas através de interfaces

## 👤 Autor

- **André Luis da Silva Reis**
- 📧 RA: 1987363
- **Gustavo Henrique Vieira da Silva**
- 📧 RA: 1992080
- **Joaquim Fernando**
- 📧 RA: 1993917
