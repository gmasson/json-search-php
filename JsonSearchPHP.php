<?php
/**
 * JSON Search PHP 1.0
 * 
 * Classe PHP projetada para facilitar a exibição, busca e a paginação de dados armazenados em um arquivo JSON, é uma ferramenta útil para realizar buscas em dados de arquivos JSON de forma segura e eficiente.
 * 
 * @author Gabriel Masson
 * @license MIT License
 */

class JsonSearchPHP {
	/**
	 * @var array $data Os dados JSON armazenados na classe.
	 */
	private $data;

	/**
	 * @var int $itemsPerPage O número de itens por página para a paginação.
	 */
	private $itemsPerPage;

	/**
	 * @var array $searchFields Os campos do registro JSON que serão utilizados para a busca.
	 */
	private $searchFields;

	/**
	 * @var string|null $cacheFile O caminho para o arquivo de cache, se aplicável.
	 */
	private $cacheFile;

	/**
	 * Construtor da classe JsonSearchPHP.
	 * Define o caminho do arquivo de cache, utilizando sys_get_temp_dir() se disponível.
	 */
	public function __construct() {
		if (function_exists('sys_get_temp_dir')) {
			$tempDir = sys_get_temp_dir();
			$this->cacheFile = $tempDir . '/JsonSearchPHP_json_cache.txt';
		} else {
			#print("sys_get_temp_dir pode ser utilizada"); // Teste para servidor, apagar else em produção
		}
	}
	/**
	 * Define a duração do cache em segundos.
	 * 
	 * @param int $cacheDuration Duração do cache em segundos
	 */
	public function setCacheDuration($cacheDuration) {
		$this->cacheDuration = $cacheDuration;
	}

	/**
	 * Define os dados JSON a serem utilizados.
	 * 
	 * @param string $jsonFile O caminho para o arquivo JSON.
	 * @throws Exception Se o arquivo JSON não puder ser carregado ou decodificado.
	 */
	public function setJsonData($jsonFile) {
		// Verifica se o cache deve ser utilizado
		if ($this->cacheFile !== null && file_exists($this->cacheFile) && (time() - filemtime($this->cacheFile)) < $this->cacheDuration) {
			$this->data = unserialize(file_get_contents($this->cacheFile));
		} else {
			// Lê e decodifica o arquivo JSON
			$jsonData = file_get_contents($jsonFile);
			$this->data = json_decode($jsonData, true);

			// Salva os dados em cache, se aplicável
			if ($this->cacheFile !== null) {
				file_put_contents($this->cacheFile, serialize($this->data));
			}
		}
	}

	/**
	 * Define o número de itens por página para a paginação
	 * 
	 * @param int $itemsPerPage Número de itens por página
	 */
	public function setItemsPerPage($itemsPerPage) {
		// Verifica se o valor é um número positivo
		if (!is_numeric($itemsPerPage) || $itemsPerPage <= 0) {
			// Define um valor padrão
			$this->itemsPerPage = 20; // Valor padrão de 20 itens por página
		} else {
			// Define o número de itens por página
			$this->itemsPerPage = intval($itemsPerPage);
		}
	}

	/**
	 * Define os campos do registro JSON que serão utilizados para a busca
	 * 
	 * @param array $searchFields Array contendo os campos para busca
	 */
	public function setSearchFields($searchFields) {
		$this->searchFields = $searchFields;
	}

	/**
	 * Sanitiza uma string ou número para prevenir ataques XSS (Cross-Site Scripting)
	 * 
	 * @param string|int $input Dados a serem sanitizados
	 * @param bool $isNumber Indica se o valor deve ser tratado como número (opcional, padrão: false)
	 * @return string|int Dados sanitizados
	 */
	public function sanitizeInput($input, $isNumber = false) {
		if ($isNumber) {
			// Se for um número, converte para inteiro com a sanitização
			return abs(intval(htmlspecialchars(trim(strip_tags($input)))));
		} else {
			// Caso contrário, aplica a sanitização normalmente
			return htmlspecialchars($input);
		}
	}

	/**
	 * Filtra os dados com base no termo de pesquisa e nos campos definidos para busca
	 * 
	 * @param string $searchTerm Termo de pesquisa
	 * @return array Dados filtrados
	 */
	private function filterData($searchTerm) {
		$filteredData = $this->data;
		if (!empty($searchTerm) && !empty($this->searchFields)) {
			$searchTerm = $this->sanitizeInput($searchTerm);
			$filteredData = array_filter($filteredData, function($item) use ($searchTerm) {
				foreach ($this->searchFields as $field) {
					if (is_array($item[$field])) {
						// Se o campo for um array, verifica se o termo de pesquisa está contido em algum elemento do array
						foreach ($item[$field] as $subItem) {
							if (stripos($subItem, $searchTerm) !== false) {
								return true;
							}
						}
					} else {
						// Se o campo for uma string, aplica a função stripos normalmente
						if (stripos($item[$field], $searchTerm) !== false) {
							return true;
						}
					}
				}
				return false;
			});
		}
		return $filteredData;
	}

	/**
	 * Obtém os resultados paginados com base na página e no termo de pesquisa
	 * 
	 * @param int $page Página atual
	 * @param string $searchTerm Termo de pesquisa
	 * @return array Array contendo os resultados paginados
	 */
	private function getPaginatedResults($page = 1, $searchTerm = '') {
		// Sanitiza o número da página atual
		$page = $this->sanitizeInput($page, true);

		// Filtra os dados com base no termo de pesquisa
		$filteredData = $this->filterData($searchTerm);
		
		// Calcula o índice inicial dos resultados para a página atual
		$startIndex = ($page - 1) * $this->itemsPerPage;
		
		// Obtém os resultados paginados a partir do índice calculado
		$paginatedData = array_slice($filteredData, $startIndex, $this->itemsPerPage);
		return $paginatedData;
	}

	/**
	 * Obtém o número total de páginas com base nos dados e no número de itens por página
	 * 
	 * @param string $searchTerm Termo de pesquisa
	 * @return int Número total de páginas
	 */
	public function getTotalPages($searchTerm = '') {
		// Filtra os dados com base no termo de pesquisa
		$filteredData = $this->filterData($searchTerm);
		
		// Calcula o número total de páginas com base no número de itens e no número de itens por página
		$totalPages = ceil(count($filteredData) / $this->itemsPerPage);
		return $totalPages;
	}

	/**
	 * Renderiza os resultados na página
	 * 
	 * @param string $searchTerm Termo de pesquisa
	 * @param int $page Página atual
	 * @param string $template Template HTML com marcadores [nomevariavel]
	 * @param string $message Mensagem de nenhum resultado encontrado
	 */
	public function renderResults($searchTerm, $page, $template, $noResultsMessage = "Nenhum resultado encontrado.") {
		// Obtém os resultados paginados com base na página e no termo de pesquisa
		$results = $this->getPaginatedResults($page, $searchTerm);
		
		if (empty($results)) {
			echo $noResultsMessage;
			return;
		}
		
		$output = '';
		foreach ($results as $result) {
			$output .= $this->renderTemplate($result, $template);
		}
		echo $output;
	}

	/**
	 * Renderiza a paginação na página
	 * 
	 * @param int $currentPage Página atual
	 * @param string $searchTerm Termo de pesquisa
	 * @param string $paginationClass Classe CSS para a paginação (opcional, padrão: pagination)
	 * @param bool $hideIfSinglePage Define se a paginação será ocultada se houver apenas uma página (opcional, padrão: false)
	 */
	public function renderPagination($searchTerm = '', $paginationClass = 'pagination', $hideIfSinglePage = false) {
		// Sanitiza o termo de pesquisa
		$searchTerm = $this->sanitizeInput($searchTerm);
		
		// Obtém o número total de páginas com base nos dados e no termo de pesquisa
		$totalPages = $this->getTotalPages($searchTerm);
		
		// Se houver apenas uma página e $hideIfSinglePage for true, retorna sem renderizar a paginação
		if ($hideIfSinglePage && $totalPages <= 1) {
			return;
		}
		
		$output = "<ul class='$paginationClass'>";
		for ($i = 1; $i <= $totalPages; $i++) {
			// Adiciona links para cada página, utilizando GET para passar o número da página
			$output .= "<li class='page-item'><a class='page-link' href='?page=$i'>$i</a></li>";
		}
		$output .= "</ul>";
		echo $output;
	}

	/**
	 * Renderiza o formulário de busca na página
	 * 
	 * @param string $searchTerm Termo de pesquisa atual
	 */
	public function renderSearchForm($searchTerm) {
		// Sanitiza o termo de pesquisa
		$searchTerm = $this->sanitizeInput($searchTerm);
		
		// Renderiza o formulário de busca com o termo atual preenchido
		$output = "<form action='' method='GET'>";
		$output .= "<input type='text' name='search' value='$searchTerm'>";
		$output .= "<button type='submit'>Buscar</button>";
		$output .= "</form>";
		echo $output;
	}

	/**
	 * Renderiza um template HTML substituindo marcadores pelos valores correspondentes
	 * 
	 * @param array $data Dados para renderizar no template
	 * @param string $template Template HTML com marcadores [nomevariavel]
	 * @return string HTML renderizado
	 */
	private function renderTemplate($data, $template) {
		$renderedTemplate = $template;
		foreach ($data as $key => $value) {
			if (is_array($value)) {
				// Se o valor for um array, converte para uma string separada por vírgulas
				$value = implode(', ', $value);
			}
			// Substitui os marcadores no template pelos valores correspondentes
			$renderedTemplate = str_replace("[$key]", htmlspecialchars($value), $renderedTemplate);
		}
		return $renderedTemplate;
	}
}