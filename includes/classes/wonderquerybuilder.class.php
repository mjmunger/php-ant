<?php


class WonderQueryBuilder 
{
  var $queryString    = NULL;
  var $queryTerms     = NULL;
  var $table          = NULL;
  var $orderBy        = NULL;
  var $groupBy        = NULL;

  var $fieldsToSearch = array();
  var $whereTerms     = array();
  var $selectColumns  = array();

  var $pdoSQL        = NULL;
  var $pdoValues     = NULL;

  function __construct($rawQueryString) {
    $this->queryString = $rawQueryString;
    $this->queryTerms = explode(' ',$this->queryString);
    $this->query = "SELECT 
                        %s
                    FROM
                        %s
                    WHERE %s";    
  }

  function addSearchField($field) {
    array_push($this->fieldsToSearch, $field);
  }

  function addSelect($column) {
    array_push($this->selectColumns, $column);
  }

  function setTable($table) {
    $this->table = $table;
  }

  function setQuery($sql) {
    $this->query = $sql;
  }

  function setGroupBy($groupBy) {
    $this->groupBy = sprintf(" GROUP BY %s ",$groupBy);
  }

  function setOrderBy($orderBy, $order = 'ASC') {
    $this->orderBy = sprintf(" ORDER BY %s %s ",$orderBy,$order);
  }

/*  function getWhere() {
    $buffer = array();

    foreach($this->fieldsToSearch as $field) {
        $condition = $this->renderQueryStringForField($field);
        array_push($buffer, $condition);
    } 

    /*Finally, join them with an OR and return it. */
    /*$return = implode(" OR ", $buffer);
    return $return;
  }*/

  function getSQL() {
    $columns = implode(', ', $this->selectColumns);
    $where = $this->renderPDOSQL();

    $sql = sprintf($this->query,$columns,$this->table,$where);

    if($this->groupBy) $sql .= $this->groupBy;
    if($this->orderBy) $sql .= $this->orderBy;

    return $sql;
  }

  /*function renderQueryStringForField() {*/
  function getWhere() {
    $terms = array();

    /* Make the terms wild */

    /*
    Take:

    levine
    lectronics

    and convert to 

    %levine%
    %lectronics%

    */


    foreach($this->queryTerms as $term) {
        $buffer = sprintf("'%%%s%%'", $term);
        array_push($terms, $buffer);
    }

    $this->queryTerms = $terms;
/*    echo "<pre>";
    //var_dump($this->queryTerms);
    echo "</pre>";*/
    unset($terms);

    /* Prepend the field we're working with */

    /*
    Take: 
    '%levine%'
    '%lectronics%'

    and convert to:
    
    `company_name` LIKE '%levine%'
    `company_name` LIKE '%lectronics%'

    */
    $terms  = array();
    $ands   = array();
    $ors    = array();
    $buffer = NULL;

    /* The ands on the inside, should be joined by ORs. */
    foreach($this->queryTerms as $term) {
      /*echo "Working with term: $term <BR>";*/
      /* These should be "ORs" */
      foreach($this->fieldsToSearch as $field) {
        $buffer = sprintf("`%s` LIKE %s", $field,$term);
        /*echo "Logic: $buffer <br>";*/
        array_push($ors, $buffer);
      }
      array_push($ands,sprintf("( %s )",implode(' OR ', $ors)));
      $ors = array();
    }

/*    echo "<pre>";
    //var_dump($ors);
    //var_dump($ands);
    echo "</pre>";*/

    $result = sprintf("( %s )",implode(' AND ', $ands));
    return $result;

    $this->queryTerms = $terms;

    /* Join them with an AND

    Take:
    `company_name` LIKE '%levine%'
    `company_name` LIKE '%lectronics%'

    and convert to:

    `company_name` LIKE '%levine%' AND `company_name` LIKE '%lectronics%'

    */

    $buffer = implode(" AND ", $this->queryTerms);

    /* Encapsulate it with parens to finish it off and return: (`company_name` LIKE '%levine%' AND `company_name` LIKE '%lectronics%') */
    $return = sprintf("(%s)",$buffer);
    array_push($this->whereTerms, $return);
    return $return;
  }


  function parenthesize($element) {
    return sprintf("( %s )",$element);
  }

  function renderPDOSQL() {
    $values = [];
    $ANDs    = [];

    //1. Loop through the fields, then the terms.
    foreach($this->fieldsToSearch as $field) {
        $buffer = [];
        foreach($this->queryTerms as $term) {

        //2. as you loop through the fields, add field like :fieldname to an array
            //$buffer[$field] = ":".$field;
            array_push($buffer,"`$field` LIKE ? ");
            //$values[$field] = sprintf("%%%s%%",$term);
            array_push($values, sprintf("%%%s%%",$term));
            
        }
        //3. join those with an AND.
        array_push($ANDs, implode(" AND ", $buffer));
    }

    //4. The resulting array of values needs to have parenthesis around each
    //element producing: (users_first LIKE :users_first OR users_first LIKE
    //:users_last)
    
    $processed = array_map([$this,'parenthesize'], $ANDs);

    //5. Join those together with an AND, and save the result to this class.
    $whereQuery = implode(" OR ", $processed);

    //echo "whereQuery:<BR>";
    //var_dump($whereQuery);
    $this->pdoSQL    = $whereQuery;
    $this->pdoValues = $values;
    return $this->pdoSQL;
    
  }

  function getPDOSQL() {
    return $this->pdoSQL;
  }

  function getPDOValues() {
    return $this->pdoValues;
  }
}