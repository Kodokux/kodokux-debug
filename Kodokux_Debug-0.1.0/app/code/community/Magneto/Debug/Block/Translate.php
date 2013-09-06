<?php

class Magneto_Debug_Block_Translate extends Magneto_Debug_Block_Abstract
{

    public function getFolders($path)
    {
        $folders = array();
        $interator = new DirectoryIterator($path);
        foreach ($interator as $file) {
            if ( ! $interator->isDot()) {
                if ($interator->isDir()) {
                    $folders[$file->getBasename()] = $file->getBasename();
                }
            }
        }
        sort($folders);
        return $folders;
    }

    public function getLocal()
    {
        return $this->getFolders(Mage::getRoot() . '/code/');
    }

    public function getNamespaces($local)
    {
        return $this->getFolders(Mage::getRoot() . "/code/{$local}/");
    }

    public function getModules($local, $namespace)
    {
        return $this->getFolders(Mage::getRoot() . "/code/{$local}/{$namespace}/");
    }

    public function executar($local, $namespace, $modulo, $linguagem)
    {
        // verifica se o módulo existe
        if ( ! is_dir(Mage::getRoot() . "/code/{$local}/{$namespace}/{$modulo}/")) {
            echo json_encode(array(
                'status' => '-1',
                'data' => $this->__('Esse módulo não existe.'),
            ));
            exit;
        }

        // arquivos e pastas que sera verificado o conteúdo
        $words = array();
        $words = array_merge($words, $this->_getFilesWords(Mage::getRoot() . "/code/{$local}/{$namespace}/{$modulo}/"));

        $words = array_merge($words, $this->_getFilesWords(Mage::getRoot() . '/design/adminhtml/base/default/layout/' . strtolower($modulo) . '/'));
        $words = array_merge($words, $this->_getFilesWords(Mage::getRoot() . '/design/adminhtml/base/default/layout/' . strtolower($namespace) . '/' . strtolower($modulo) . '/'));
        $words = array_merge($words, $this->_getFilesWords(Mage::getRoot() . '/design/adminhtml/base/default/template/' . strtolower($modulo) . '/'));
        $words = array_merge($words, $this->_getFilesWords(Mage::getRoot() . '/design/adminhtml/base/default/template/' . strtolower($namespace) . '/' . strtolower($modulo) . '/'));

        $words = array_merge($words, $this->_getFilesWords(Mage::getRoot() . '/design/adminhtml/default/default/layout/' . strtolower($modulo) . '/'));
        $words = array_merge($words, $this->_getFilesWords(Mage::getRoot() . '/design/adminhtml/default/default/layout/' . strtolower($namespace) . '/' . strtolower($modulo) . '/'));
        $words = array_merge($words, $this->_getFilesWords(Mage::getRoot() . '/design/adminhtml/default/default/template/' . strtolower($modulo) . '/'));
        $words = array_merge($words, $this->_getFilesWords(Mage::getRoot() . '/design/adminhtml/default/default/template/' . strtolower($namespace) . '/' . strtolower($modulo) . '/'));

        $words = array_merge($words, $this->_getFilesWords(Mage::getRoot() . '/design/frontend/base/default/layout/' . strtolower($modulo) . '/'));
        $words = array_merge($words, $this->_getFilesWords(Mage::getRoot() . '/design/frontend/base/default/layout/' . strtolower($namespace) . '/' . strtolower($modulo) . '/'));
        $words = array_merge($words, $this->_getFilesWords(Mage::getRoot() . '/design/frontend/base/default/template/' . strtolower($modulo) . '/'));
        $words = array_merge($words, $this->_getFilesWords(Mage::getRoot() . '/design/frontend/base/default/template/' . strtolower($namespace) . '/' . strtolower($modulo) . '/'));

        $words = array_merge($words, $this->_getFilesWords(Mage::getRoot() . '/design/frontend/default/default/layout/' . strtolower($modulo) . '/'));
        $words = array_merge($words, $this->_getFilesWords(Mage::getRoot() . '/design/frontend/default/default/layout/' . strtolower($namespace) . '/' . strtolower($modulo) . '/'));
        $words = array_merge($words, $this->_getFilesWords(Mage::getRoot() . '/design/frontend/default/default/template/' . strtolower($modulo) . '/'));
        $words = array_merge($words, $this->_getFilesWords(Mage::getRoot() . '/design/frontend/default/default/template/' . strtolower($namespace) . '/' . strtolower($modulo) . '/'));

        // Verifica se existe alguma palavra para ser traduzida
        if (empty($words)) {
            echo json_encode(array(
                'status' => '-1',
                'data' => $this->__('Não existem palavras para serem traduzidas.'),
            ));
            exit;
        }

        $words = $this->_clearArray($words);

        // faz a chave e o valor do array serem os mesmos
        $words = array_combine($words, $words);

        // verifica se foi passado alguma linguagem
        if ($linguagem === '') {
            echo json_encode(array(
                'status' => '-1',
                'data' => $this->__('Não foi passado nenhuma linguagem.'),
            ));
            exit;
        }

        $linguagem = explode(',', $linguagem);

        $retorno = $this->__('Arquivos gerados: ');
        foreach ($linguagem as $locale) {
            $locale = trim($locale);

            if ( ! is_dir(Mage::getRoot() . "/locale/{$locale}")) {
                mkdir(Mage::getRoot() . "/locale/{$locale}");
            }

            $diferenca = array();
            $diferenca = $this->removeExistsWords($words, Mage::getRoot() . "/locale/{$locale}");

            // se já exitir um arquivo, então mescla com o mesmo
            if (is_file(Mage::getRoot() . "/locale/{$locale}/{$namespace}_{$modulo}.csv")) {
                $csvArray = $this->_csvToArray(Mage::getRoot() . "/locale/{$locale}/{$namespace}_{$modulo}.csv");
                $diferenca = array_merge($diferenca, $csvArray);
            }

            uasort($diferenca, 'strnatcasecmp');

            $csv = $this->_arrayToCsv($diferenca);
            file_put_contents(Mage::getRoot() . "/locale/{$locale}/{$namespace}_{$modulo}.csv", $csv);
            $retorno .= '<br />' . Mage::getRoot() . "/locale/{$locale}/{$namespace}_{$modulo}.csv";
        }
        
        echo json_encode(array(
            'status' => '1',
            'data' => $retorno,
        ));
    }

    /**
     * Escaneia os diretórios e pega um array de palavras deles
     * 
     * @param string $path Caminho para o diretório a ser escaniado
     * @return array Palavras dos arquivos 
     */
    private function _getFilesWords($path)
    {
        if ( ! is_dir($path)) {
            return array();
        }
        $iterator = new RecursiveDirectoryIterator($path);
        $recursiveIterator = new RecursiveIteratorIterator($iterator);

        $words = array();
        foreach ($recursiveIterator as $entry) {
            /* @var $entry SplFileInfo */
            if ( ! $entry->isDir()) {
                if ($entry->getExtension() == 'xml') {
                    $words = array_merge($words, $this->_searchXmlWords(file_get_contents($entry->getPathname())));
                } else {
                    $words = array_merge($words, $this->_searchPhpWords(file_get_contents($entry->getPathname())));
                }
            }
        }

        return $words;
    }

    /**
     * Retira valores desnecessários do array para gerar o csv
     * 
     * @param array $array
     * @return array
     */
    private function _clearArray(array $array)
    {
        foreach ($array as &$item) {
            $item = str_ireplace('<![CDATA[', '', $item);
            $item = str_ireplace(']]>', '', $item);
        }

        return $array;
    }

    /**
     * Converte um array em uma string formatada em CSV
     * 
     * @param type $array
     * @return string 
     */
    private function _arrayToCsv($array)
    {
        $csv = '';
        foreach ($array as $key => $item) {
            $csv.= '"' . $key . '",' . '"' . $item . '"' . PHP_EOL;
        }
        return $csv;
    }

    /**
     * Procura por palavras no texto para serem traduzidas
     * 
     * @param type $conteudo
     * @return type
     */
    private function _searchPhpWords($conteudo)
    {
        $doubleQuotes = array();
        preg_match_all("/__\('(.*?)'/", $conteudo, $doubleQuotes);

        $singleQuote = array();
        preg_match_all('/__\("(.*?)"/', $conteudo, $singleQuote);

        $array = array_merge($doubleQuotes[1], $singleQuote[1]);

        return $array;
    }

    /**
     * Procura por palavras no texto somente de arquivo .xml para serem traduzidas
     * 
     * @param type $conteudo
     * @return type
     */
    private function _searchXmlWords($conteudo)
    {
        $nameTag = array();
        preg_match_all('/<name>(.*?)<\/name>/', $conteudo, $nameTag);

        $labelTag = array();
        preg_match_all('/<label>(.*?)<\/label>/', $conteudo, $labelTag);

        $titleTag = array();
        preg_match_all('/<title>(.*?)<\/title>/', $conteudo, $titleTag);

        $commentTag = array();
        preg_match_all('/<comment>(.*?)<\/comment>/', $conteudo, $commentTag);

        $descriptionTag = array();
        preg_match_all('/<description.*?>(.*?)<\/description>/', $conteudo, $descriptionTag);

        $array = array_merge($nameTag[1], $labelTag[1], $titleTag[1], $commentTag[1], $descriptionTag[1]);

        return $array;
    }

    /**
     * Converte um arquivo CSV em uma array
     * 
     * @param type $path
     * @return type
     */
    private function _csvToArray($path)
    {
        $array = array();
        $fp = fopen($path, 'r');
        if ($fp) {
            while ( ! feof($fp)) {
                $row = fgetcsv($fp);
                if (isset($row[1])) {
                    $array[$row[0]] = $row[1];
                }
            }

            fclose($fp);
        }

        return $array;
    }

    /**
     * Remove as palavras que já tem tradução
     * 
     * @param array $words palavras encontradas nos arquivos
     * @param array $path caminho do locale
     * @return array
     */
    public function removeExistsWords($words, $path)
    {
        $iterator = new RecursiveDirectoryIterator($path);
        $recursiveIterator = new RecursiveIteratorIterator($iterator);

        $csvArray = array();
        foreach ($recursiveIterator as $entry) {
            /* @var $entry SplFileInfo */
            if ( ! $entry->isDir()) {
                if ($entry->getExtension() == 'csv') {
                    $csvArray = array_merge($csvArray, $this->_csvToArray($entry->getPathname()));
                }
            }
        }

        $diff = array_diff($words, $csvArray);
        
        return $diff;
    }

}

