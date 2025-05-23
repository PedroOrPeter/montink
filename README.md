![image](https://github.com/user-attachments/assets/422b5cd8-1669-4f4a-a523-75029b171df6)# Sistema de Cadastro de Produtos com Estoque e Carrinho

Este projeto é um sistema simples de cadastro, edição e gerenciamento de produtos com suporte a variações e controle de estoque, além de permitir adicionar produtos a um carrinho de compras via sessão PHP.

## 📦 Funcionalidades

- Cadastro de novos produtos
- Edição de produtos existentes
- Adição de variações (ex: tamanhos, cores)
- Controle de estoque por variação ou produto único
- Carrinho de compras com persistência em sessão
- Atualização de estoque automática ao adicionar ao carrinho
- Visualização de produtos e estoques
- Interface web com Bootstrap 5

## 🛠️ Tecnologias

- PHP (sem framework)
- MySQL (via PDO)
- HTML5, CSS3
- Bootstrap 5.3 (CDN)
- JavaScript (para adicionar/remover variações dinamicamente)

## 📁 Estrutura de Arquivos
/config
└── database.php # Classe Database para conexão PDO
/app/ pasta guardando corpo e estrutura do projeto
└── controllers
  └── Arquivos para trabalhar com JSON, preparados para atuarem como API + tratamento
└── models
  └── Arquivos para trabalhar com dados do banco de dados
└── utils
  └── Contém chave de segurança para webhook
└── views
  └── Páginas para serem exibidas e transmitirem dados para o usuário e também para nossos controllers
└── webhook
  └── Arquivos para trabalhar com notificações de APIs externas
/config/
└── Database.php # Conexão PDO
/index.php # Página principal com cadastro, listagem e carrinho
