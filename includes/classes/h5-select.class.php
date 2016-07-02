<?php
/**
 * Represents and creates an HTML5 dropdown from a database query.
 **/

/**
 * Represents and creates an HTML5 dropdown from a database query.
 *
 * This class instantiates a database connection 
 *
 * @package      BFW Toolkit
 * @subpackage   Core
 * @category     Utility Classes
 * @author       Michael Munger <michael@highpoweredhelp.com>
 */ 

class Html5select
{
    /**
    * @var string $sql SQL query that is responsible for getting the options. 
    **/
    
    var $sql            = '';

    /**
    * @var object $db MySQLi Object to connect to DB. Use gimmieDB() to get one. 
    **/
    
    var $db             = '';

    /**
    * @var string $idColumn Holds the column name that has the unique ID for the option. 
    **/
    
    var $idColumn       = '';

    /**
    * @var string $dataColumn Holds the data that is actually displayed. 
    **/
    
    var $dataColumn     = '';

    /**
    * @var string $dataColumn2 Holds a secondary column name, in case we need to show two columns as a value. (Think: Firstname + Lastname) 
    **/
    
    var $dataColumn2    = '';

    /**
     * Instantiates an object of this class.
     * Example:
     *
     * <code>
     * $db = gimmieDB();
     * $dd = new Html5Select($db);
     * $dd->sql = 'SELECT * FROM users';
     * $dd->idColumn = 'users_id';
     * $dd->dataColumn = 'users_first';
     * $dd->dataColumn2 = 'users_last';
     * $rawHTML = $dd->getHTML();
     * echo $rawHTML;
     * </code>
     *
     * @return return value
     * @param param
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function __construct($db=false)
    {
        if($db) {
            $this->db = $db;
        } else {
            $this->db = gimmieDB();
        }

        if($this->db->connect_error) {
            die( "Html5select connection error when connecting to database. Error(" . $this->db->connect_errno.") ".$this->db->connect_error);
        } else {
            //it worked. Do nothing.
        }
    }

    /**
     * Renders the HTML5 dropdown.
     * Example:
     *
     * <code>
     * $js = array('js'=>'javascript:onClick(alert("Hello!"));');
     * echo $dd->getHTML($js);
     * </code>
     *
     * @return string the rendered HTML dropdown.
     * @param array $attr Optional. An array of attributes that will be added to the <select> element. 
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/
    
    function getHTML($attr = '')
    {
        //returns a well formed HTML 5 select
        //$attr is an ARRAY of attributes that will be added to the select.
        $buffer = "";
        $header = "<select>\n";
        if(sizeof($attr)>0) {
            foreach($attr as $key=>$value) {
                $buffer .= " " . $value;
            }
            $header = sprintf("<select %s>\n",$buffer);
        }
        
        $options = '';
        //Get all the options.
        $result = $this->db->query($this->sql);
        while($row = $result->fetch_assoc()) {
            if(strlen($this->dataColumn2)>0) {
                $options .= sprintf("\t<option value=\"%s\">%s %s</option>\n",$row[$this->idColumn],$row[$this->dataColumn],$row[$this->dataColumn2]);
            } else {
                $options .= sprintf("\t<option value=\"%s\">%s</option>\n",$row[$this->idColumn],$row[$this->dataColumn]);                
            }
        }
        $control = $header.$options."</select>";
        
        return $control;
    }
}
?>