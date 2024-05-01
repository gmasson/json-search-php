# JSON Search PHP 1.0

O **JSON Search PHP** é uma classe PHP projetada para facilitar a exibição, busca e a paginação de dados armazenados em um arquivo JSON, é uma ferramenta útil para realizar buscas em dados de arquivos JSON de forma segura e eficiente. Com essa classe, é possível definir os dados JSON a serem utilizados, configurar o número de itens por página, especificar os campos de busca e renderizar os resultados de forma paginada.


## Métodos disponíveis

- `setJsonData($jsonFile)`: Define os dados JSON a serem utilizados.
- `setCacheDuration($cacheDuration)`: Define o tempo de duração do cache em segundos (opcional, por padrão é 1800 segundos, ou seja, 30 minutos).
- `setItemsPerPage($itemsPerPage)`: Define o número de itens por página para a paginação.
- `setSearchFields($searchFields)`: Define os campos do registro JSON que serão utilizados para a busca.
- `renderResults($searchTerm, $page, $template, $noResultsMessage)`: Renderiza os resultados na página.
- `renderPagination($searchTerm, $paginationClass, $hideIfSinglePage)`: Renderiza a paginação na página.
- `renderSearchForm($searchTerm)`: Renderiza o formulário de busca na página.


## Como usar

### 1. Instanciando a classe

```php
// Cria uma instância da classe JsonSearchPHP
$jsonSearchPHP = new JsonSearchPHP();
```

### 2. Definindo o tempo de duração do cache (opcional)

Caso seu servidor permita o uso da função para arquivos temporários `sys_get_temp_dir`, a classe irá criar um arquivo de cache de seu arquivo json, para agilizar as consultas dos dados.

Se desejar, pode definir em segundos, um tempo maior ou menor para a duração do cache, que por padrão é 30 minutos (1800 segundos)

```php
// Define tempo de duração do cache em 1 hora
$jsonSearchPHP->setCacheDuration(3600);
```

### 3. Definindo os dados JSON

Antes de usar a classe, você precisa definir o arquivo JSON que serão utilizados. Isso é feito utilizando o método `setJsonData()` e passando o caminho para o arquivo JSON como argumento.

```php
$jsonSearchPHP->setJsonData('caminho/para/dados.json');
```

### 4. Configurando a paginação

Você pode definir o número de itens por página utilizando o método `setItemsPerPage()`.

```php
$jsonSearchPHP->setItemsPerPage(10); // Define 10 itens por página
```

### 5. Especificando os campos de busca

É necessário definir os campos do registro JSON que serão utilizados para a busca. Isso é feito passando um array contendo os nomes dos campos como argumento para o método `setSearchFields()`.

```php
$jsonSearchPHP->setSearchFields(['nome', 'categorias']); // Define os campos 'nome' e 'categorias'
```

### 6. Renderizando resultados e paginação

Para renderizar os resultados e a paginação na página, utilize os métodos `renderResults()` e `renderPagination()`.

```php
$page = isset($_GET['page']) ? $_GET['page'] : 1; // Obtém a página atual da URL
$searchTerm = isset($_GET['search']) ? $_GET['search'] : ''; // Obtém o termo de pesquisa da URL

// Define o template HTML com marcadores [nomevariavel]
$template = "
<div>
    <p><strong>ID:</strong> [id]</p>
    <p><strong>Nome:</strong> [nome]</p>
    <p><strong>Descrição:</strong> [descricao]</p>
    <p><strong>Preço:</strong> [preco]</p>
</div>
<hr>
";

// Renderiza os resultados utilizando o template HTML
$jsonSearchPHP->renderResults($searchTerm, $page, $template);

// Renderiza a paginação
$jsonSearchPHP->renderPagination($searchTerm);
```

O **JSON Search PHP** oferece flexibilidade ao permitir que você defina as variáveis do JSON que serão visualizadas diretamente no momento de renderização dos resultados. Isso significa que você pode controlar quais campos específicos dos seus dados JSON serão exibidos na interface do usuário sem precisar alterar a estrutura dos dados ou modificar a lógica interna da classe.

Essa flexibilidade é alcançada por meio do uso de um template HTML com marcadores, onde cada marcador corresponde a uma variável do JSON. Por exemplo, suponha que você tenha um campo chamado "nome" em seus dados JSON e deseja exibi-lo na interface. Você pode definir um marcador `[nome]` no seu template HTML, e a função substituirá esse marcador pelo valor correspondente do campo "nome" de cada item do JSON durante a renderização.

Exemplo de um JSON que será realizada a busca:

```json
[
    {
        "id": 1,
        "nome": "Produto 1",
        "descricao": "Descrição do Produto 1",
        "preco": 10.99,
        "categorias": ["Eletrônicos", "Celulares"]
    },
    {
        "id": 2,
        "nome": "Produto 2",
        "descricao": "Descrição do Produto 2",
        "preco": 24.99,
        "categorias": ["Moda", "Roupas"]
    },
    {
        "id": 3,
        "nome": "Produto 3",
        "descricao": "Descrição do Produto 3",
        "preco": 39.99,
        "categorias": ["Livros", "Ficção"]
    }
]
```

Aqui está um exemplo de template HTML com marcadores:

```html
<div>
    <p><strong>ID:</strong> [id]</p>
    <p><strong>Nome:</strong> [nome]</p>
    <p><strong>Descrição:</strong> [descricao]</p>
    <p><strong>Preço:</strong> [preco]</p>
</div>
<hr>
```

Neste exemplo, `[id]`, `[nome]`, `[descricao]` e `[preco]` são marcadores que serão substituídos pelos valores correspondentes de cada item do registro do JSON, durante a renderização.

Ao utilizar o método `renderResults()`, você fornece esse template HTML como argumento juntamente com os dados de pesquisa, página atual e termo de pesquisa. A classe então substitui os marcadores pelos valores correspondentes do JSON e renderiza os resultados de acordo com o template fornecido.

### 7. Renderizando o formulário de busca (opcional)

Se desejar incluir um formulário de busca na página, pode utilizar o método opcional `renderSearchForm()`.

```php
// Renderiza o formulário de busca com o termo atual preenchido
$jsonSearchPHP->renderSearchForm($searchTerm);
```

ou criar seu próprio formulário de pesquisa, definindo o input name como `search` ou com o nome que utilizar para o `searchTerm`


## Exemplo de uso

Um exemplo completo de uso da classe é fornecido no código PHP.

```php
<?php
// Exemplo de uso:

require "JsonSearchPHP.php";

// Cria uma instância da classe JsonSearchPHP
$jsonSearchPHP = new JsonSearchPHP();

// Define os dados JSON a serem utilizados
$jsonSearchPHP->setJsonData('dados.json');

// Define o número de itens por página para a paginação
$jsonSearchPHP->setItemsPerPage(3);

// Define os campos do registro JSON que serão utilizados para a busca
$jsonSearchPHP->setSearchFields(['nome', 'categorias']);

// Obtém a página atual da URL
$page = isset($_GET['page']) ? $_GET['page'] : 1;

// Obtém o termo de pesquisa da URL
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Define o template HTML com marcadores [nomevariavel]
$template = "
<div>
    <p><strong>ID:</strong> [id]</p>
    <p><strong>Nome:</strong> [nome]</p>
    <p><strong>Descrição:</strong> [descricao]</p>
    <p><strong>Preço:</strong> [preco]</p>
</div>
<hr>
";

// Renderiza o formulário de busca (opcional)
$jsonSearchPHP->renderSearchForm($searchTerm);

// Renderiza os resultados utilizando o template HTML
$jsonSearchPHP->renderResults($searchTerm, $page, $template);

// Renderiza a paginação
$jsonSearchPHP->renderPagination($searchTerm);
```

O exemplo acima está sem sanitização, e apesar da classe ter algumas sanitizações internas por padrão, é recomendado que sempre faça sanitizações para entradas de dados como `$_get[]`, para suas aplicações em produção, no caso acima, para `$page` e `$searchTerm`, evitando ataques como XSS (Cross-Site Scripting).


## Licença

Este projeto está licenciado sob os termos da Licença MIT.