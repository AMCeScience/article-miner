<?php 

class PubMedAPI {
  // Pagination
  public $limit = 10;
  private $start = 0;
  private $page = 1;

  // Currently only PubMed available
  private $search_database = 'pubmed';
  // For available $data_type settings refer to: 
  // https://www.ncbi.nlm.nih.gov/books/NBK25499/table/chapter4.T._valid_values_of__retmode_and/?report=objectonly
  public $data_type = 'xml';

  // Expose the data objects
  public $esearch_data = null;
  public $efetch_data = null;

  // Search mode, either search (query the API) or fetch (retrieve articles from API)
  private $search_mode = 'search';
  // API url strings
  private $esearch = 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?';
  private $efetch = 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?';

  private $search_data_type = '';
  private $search_params = array();
  private $search_term = '';
  private $search_url = '';
  private $search_url_params = '';

  // Used for debugging, prints request url for API
  private $show_urls = true;

  // Available modes used for displaying data or returning data
  // Display mode:
  //  - false: do not display
  //  - raw: echo raw data
  //  - print: echo parsed data
  // Return mode:
  //  - raw: return raw data, type depends on $data_type
  //  - parsed: return parsed data, see Search_data or Fetch_data
  public $display_mode = 'raw';
  public $return_mode = 'parsed';

  private $supported_display_modes = array(false, 'raw', 'print');
  private $supported_return_modes = array('raw', 'parsed');

  public function __construct() {
    if (!in_array($this->display_mode, $this->supported_display_modes)) {
      throw new Exception("Display mode not supported.");
    }

    if (!in_array($this->return_mode, $this->supported_return_modes)) {
      throw new Exception("Return mode not supported.");
    }
  }

  public function search($term, $page = 1, $callback = array('PubMedAPI', '_fetch')) {
    $this->_set_pagination($page);

    $this->_set_search_mode('search');

    $result = $this->esearch($term);

    if ($callback !== false) {
      return call_user_func($callback, $result);
    }
    
    return $result;
  }

  protected function esearch($term) {
    $this->search_term = $term;

    // Setup the URL for esearch
    $this->_build_url_params();

    // Get the data
    $results = $this->_retrieve_data();    
    
    // Return data
    $retrieved = count($results->ids);

    $return_result = array(
      "ids"                   => $results->ids,
      "page"                  => $this->page,
      "from"                  => $this->start,
      "to"                    => $this->start + $retrieved,
      "total_pages"           => ceil($results->total_number_of_results / $this->limit),
      "result_count"          => $retrieved,
      "total_results"         => $results->total_number_of_results,
      "query_interpretation"  => $results->query_translation
    );

    // Print data if display_mode is set
    if ($this->display_mode === 'print') {
      $this->pretty_print($return_result);
    }

    // Results of esearch
    return $return_result;
  }

  private function _fetch($result) {
    $ids = $result['ids'];
    
    return $this->fetch($ids);
  }

  public function fetch($ids, $compact = false, $callback = false) {
    $this->_set_search_mode('fetch');

    $result = $this->efetch($ids);

    if ($callback !== false) {
      return call_user_func($callback, $result);
    }
    
    return $result;
  }

  protected function efetch($ids) {
    $this->fetch_ids = $ids;

    // Setup the URL for efetch
    $this->_build_url_params();
    
    $result = $this->_retrieve_data();

    if ($this->display_mode === 'print') {
      $this->pretty_print($result);
    }

    return $result;
  }

  private function _set_pagination($page) {
    $this->page = $page;
    $this->start = ($page - 1) * $this->limit;
  }

  private function _set_search_mode($mode) {
    $this->search_mode = $mode;

    switch ($mode) {
      case 'fetch':
        $this->search_url = $this->efetch;

        break;
      case 'search':
        $this->search_url = $this->esearch;

        break;
    }
  }

  private function _build_params() {
    $search = array(' ');
    $replace = array('+');

    $params = array(
      'db'        => $this->search_database,
      'retmode'   => $this->data_type,
      'retmax'    => $this->limit
    );

    switch ($this->search_mode) {
      case 'fetch':
        $params['id'] = implode(',', $this->fetch_ids);
        $params['rettype'] = 'null';
        $params['retmode'] = 'xml';

        break;
      case 'search':
        $params['retstart'] = $this->start;
        $params['term'] = str_replace($search, $replace, stripslashes($this->search_term));

        break;
    }

    $this->search_params = $params;
  }

  private function _build_url_params() {
    $this->_build_params();

    $string_params = array_map(function($key, $value) {
      return $key . '=' . $value;
    }, array_keys($this->search_params), $this->search_params);

    $httpquery = implode('&', $string_params);

    $this->search_url_params = $httpquery;
  }

  private function _get_url() {
    if ($this->show_urls === true) {
      echo $this->search_url . $this->search_url_params;
    }

    return $this->search_url . $this->search_url_params;
  }

  private function _retrieve_data() {
    $data = $this->_load_file_from_url($this->_get_url());

    switch ($this->data_type) {
      case 'asn.1':
        if ($this->display_mode === 'raw') {
          $this->pretty_print($data);
        }

        if ($this->return_mode === 'raw') {
          return $data;
        }

        break;
      case 'json':
        if ($this->display_mode === 'raw') {
          $this->pretty_print($data);
        }

        if ($this->return_mode === 'raw') {
          return json_decode($data);
        }

        break;
      case 'xml':
        if ($this->display_mode === 'raw') {
          header('Content-type: text/xml');

          echo $data;
        }

        if ($this->return_mode === 'raw') {
          return simplexml_load_string($data);
        }

        break;
    }

    $return_data = null;

    if ($this->return_mode === 'parsed') {
      switch ($this->search_mode) {
        case 'fetch':
          $return_data = new Fetch_data($data);

          $this->efetch_data = $return_data;

          break;
        case 'search':
          $return_data = new Search_data($data);

          $this->esearch_data = $return_data;

          break;
      }
    }
    
    return $return_data;
  }

  private function _load_file_from_url($url) {
    $curl = curl_init($url);
    
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
    $str = curl_exec($curl);
    
    curl_close($curl);

    return $str;
  }

  public function pretty_print($data) {
    echo '<pre><code>';
    print_r($data);
    echo '</pre></code>';
  }
}

class Search_data {
  public $raw;
  public $ids;
  public $total_number_of_results;
  public $query_translation;

  function __construct($raw = null) {
    $this->raw = $raw;

    if (isset($this->raw)) {
      return $this->_detect_type();
    }
  }

  private function _detect_type() {
    $result = null;

    if (!isset($this->raw)) {
      throw new Exception('Results could not be retrieved.');
    }
    
    libxml_use_internal_errors(true);

    if ($xml = simplexml_load_string($this->raw)) {
      $this->raw = $xml;
      $result = $this->parse_xml($this->raw);
    } else if (($json = json_decode($this->raw)) !== null && !preg_match('/[^,:{}\\[\\]0-9.\\-+Eaeflnr-u \\n\\r\\t]/', preg_replace('/"(\\.|[^"\\\\])*"/', '', $this->raw))) {
      $this->raw = $json;
      $result = $this->parse_json($this->raw);   
    } else {
      throw new Exception('Data type not supported.');
    }

    return $result;
  }

  function parse_json($json) {
    if (isset($json->esearchresult->idlist) && !empty($json->esearchresult->idlist)) {
      $this->ids = $json->esearchresult->idlist;
    }

    $this->total_number_of_results = (int) $json->esearchresult->count;
    $this->query_translation = $json->esearchresult->querytranslation;

    return $this;
  }

  function parse_xml($xml) {
    if (isset($xml->IdList->Id) && !empty($xml->IdList->Id)) {
      $this->ids = array_map(function($id) {
        return (int) $id;
      }, (array) $xml->IdList->Id);
    }

    $this->total_number_of_results = (int) $xml->Count;
    $this->query_translation = (string) $xml->QueryTranslation;

    return $this;
  }
}

class Fetch_data {
  public $raw;
  public $articles;

  function __construct($raw) {
    $this->raw = $raw;
    
    if (!isset($this->raw)) {
      throw new Exception('Results could not be retrieved.');
    }
    
    libxml_use_internal_errors(true);

    if ($xml = simplexml_load_string($this->raw)) {
      $this->raw = $xml;
      $result = $this->parse_xml($this->raw);
    } else {
      throw new Exception('Data type not supported.');
    }

    return $result;
  }

  function parse_xml($xml) {
    $data = array();

    // echo '<pre><code>';
    // print_r($xml);
    // echo '</pre></code>';

    foreach ($xml->PubmedArticle as $article) {
      $medline_citation = $article->MedlineCitation;
      $pubmed_data = $article->PubmedData;

      $id = $medline_citation->PMID;
      $article_data = $medline_citation->Article;
      $mesh_data = $medline_citation->MeshHeadingList;

      // Authors array contains concatendated LAST NAME + INITIALS
      // TODO: whats going on eh'?
      $authors = array();
      if (isset($article_data->AuthorList->Author)) {
        try {
          foreach ($article_data->AuthorList->Author as $k => $a) {
            $authors[] = (string)$a->LastName .' '. (string)$a->Initials;
          }
        } catch (Exception $e) {
          $a = $article_data->AuthorList->Author;
          $authors[] = (string)$a->LastName .' '. (string)$a->Initials;
        }
      }

      // Keywords array
      $keywords = array();
      if (isset($mesh_data->MeshHeading)) {
        foreach ($mesh_data->MeshHeading as $mesh_heading) {
          $keywords[] = (string) $mesh_heading->DescriptorName;

          if (isset($mesh_heading->QualifierName)) {
            if (is_array($mesh_heading->QualifierName)) {
              $keywords = array_merge($keywords, $mesh_heading->QualifierName);
            } else {
              $keywords[] = (string) $mesh_heading->QualifierName;
            }
          }
        }
      }

      // Article IDs array
      $articleid = array();
      if (isset($pubmed_data->ArticleIdList)) {
        foreach ($pubmed_data->ArticleIdList->ArticleId as $id) {
          $articleid[] = $id;
        }
      }

      $this->articles[] = new Fetch_article(
        (string) $id,
        (string) $article_data->Journal->JournalIssue->Volume,
        (string) $article_data->Journal->JournalIssue->Issue,
        (string) $article_data->Journal->JournalIssue->PubDate->Year,
        (string) $article_data->Journal->JournalIssue->PubDate->Month,
        (string) $article_data->Journal->ISSN,
        (string) $article_data->Journal->Title,
        (string) $article_data->Journal->ISOAbbreviation,
        (string) $article_data->Pagination->MedlinePgn,
        (string) $article_data->ArticleTitle,
        (string) $article_data->Abstract->AbstractText,
        (string) $article_data->Affiliation,
        $authors,
        implode(',', $articleid),
        $keywords
      );
    }

    return $this;
  }
}

class Fetch_article {
  public $pmid;
  public $volume;
  public $issue;
  public $year;
  public $month;
  public $pages;
  public $issn;
  public $journal;
  public $journalabbrev;
  public $title;
  public $abstract;
  public $affiliation;
  public $authors;
  public $articleid;
  public $keywords;

  function __construct($pmid, $volume, $issue, $year, $month, $pages, $issn, $journal, $journalabbrev, $title, $abstract, $affiliation, $authors, $articleid, $keywords) {
    $this->pmid = $pmid;
    $this->volume = $volume;
    $this->issue = $issue;
    $this->year = $year;
    $this->month = $month;
    $this->pages = $pages;
    $this->issn = $issn;
    $this->journal = $journal;
    $this->journalabbrev = $journalabbrev;
    $this->title = $title;
    $this->abstract = $abstract;
    $this->affiliation = $affiliation;
    $this->authors = $authors;
    $this->articleid = $articleid;
    $this->keywords = $keywords;

    return $this;
  }
}