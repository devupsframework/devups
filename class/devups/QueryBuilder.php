<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of QueryBuilder
 *
 * @author Aurelien Atemkeng
 */
class QueryBuilder extends \DBAL {

//    private $table;    
    private $query = "";
    private $parameters = [];
    private $columns = "*";
    private $defaultjoin = "";
    private $columnscount = "COUNT(*)";
    private $endquery = "";
    private $initwhereclause = false;
    private $defaultjoinsetted = false;

    /**
     * All of the available clause operators.
     *
     * @var array
     */
    private $operators = [
        '=', '<', '>', '<=', '>=', '<>', '!=', '<=>',
        'like', 'like binary', 'not like', 'between', 'ilike', 'is',
        '&', '|', '^', '<<', '>>',
        'rlike', 'regexp', 'not regexp',
        '~', '~*', '!~', '!~*', 'similar to',
        'not similar to', 'not ilike', '~~*', '!~~*',
    ];

    public function __construct($entity = null) {
        $this->initwhereclause = false;
        if ($entity)
            parent::__construct($entity);

//        $this->table = strtolower(get_class($entity));
    }

    /**
     * Add an array of where clauses to the query.
     *
     * @param  array  $column
     * @param  string  $boolean
     * @param  string  $method
     * @return $this
     */
//    protected function addArrayOfWheres($column, $boolean, $method = 'where')
//    {
//        return $this->whereNested(function ($query) use ($column, $method, $boolean) {
//            foreach ($column as $key => $value) {
//                if (is_numeric($key) && is_array($value)) {
//                    $query->{$method}(...array_values($value));
//                } else {
//                    $query->$method($key, '=', $value, $boolean);
//                }
//            }
//        }, $boolean);
//    }

    /**
     * Prepare the value and operator for a where clause.
     *
     * @param  string  $value
     * @param  string  $operator
     * @param  bool  $useDefault
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function prepareValueAndOperator($value, $operator, $useDefault = false) {
        if ($useDefault) {
            return [$operator, '='];
        } elseif ($this->invalidOperatorAndValue($operator, $value)) {
            throw new InvalidArgumentException('Illegal operator and value combination.');
        }

        return [$value, $operator];
    }

    /**
     * Determine if the given operator and value combination is legal.
     *
     * Prevents using Null values with invalid operators.
     *
     * @param  string  $operator
     * @param  mixed  $value
     * @return bool
     */
    protected function invalidOperatorAndValue($operator, $value) {
        return is_null($value) && in_array($operator, $this->operators) &&
                !in_array($operator, ['=', '<>', '!=']);
    }

    /**
     * Determine if the given operator is supported.
     *
     * @param  string  $operator
     * @return bool
     */
    protected function invalidOperator($operator) {
        return !in_array(strtolower($operator), $this->operators, true) &&
                !in_array(strtolower($operator), $this->grammar->getOperators(), true);
    }

    /**
     * Set the columns to be selected.
     *
     * @param  array|mixed  $columns
     * @return $this
     */
    public function addselect($columns = "*", $object = null, $defaultjoin = true) {

        // save currente sequence
        $this->querysequence = $this->query;
        $this->sequenceobject = $this->object;

        if (is_object($columns)):
            $this->instanciateVariable($columns);
            $columns = "*";
        elseif (is_bool($object)):
            $defaultjoin = $object;
        elseif (is_object($object)):
            $this->instanciateVariable($object);
        endif;

        $this->defaultjoinsetted = $defaultjoin;
        $this->query = " select $columns from `" . $this->table . "` ";

        if ($defaultjoin) {

            if (!empty($this->entity_link_list)) {
                foreach ($this->entity_link_list as $entity_link) {
                    $this->query .= " left join `" . strtolower(get_class($entity_link)) . "` on " . strtolower(get_class($entity_link)) . ".id = " . $this->table . "." . strtolower(get_class($entity_link)) . "_id";
                }
            }
        }

        return $this;
    }

    public function close() {
//        $params = $this->parameters;
        $query = $this->query;
        
        // restaure sequence before any other select
        $this->query = $this->querysequence;
        
        $this->instanciateVariable($this->sequenceobject);

        return $query;
    }

    private function initdefaultjoin() {

        if (!empty($this->entity_link_list)) {
            foreach ($this->entity_link_list as $entity_link) {
                $this->defaultjoin .= " left join `" . strtolower(get_class($entity_link)) . "` on " . strtolower(get_class($entity_link)) . ".id = " . $this->table . "." . strtolower(get_class($entity_link)) . "_id";
            }
        }
    }

    /**
     * Set the columns to be selected.
     *
     * @param  array|mixed  $columns
     * @return $this
     */
    public function join() {
        $this->defaultjoinsetted = true;
        if (!empty($this->entity_link_list)) {
            foreach ($this->entity_link_list as $entity_link) {
                $this->defaultjoin .= " left join `" . strtolower(get_class($entity_link)) . "` on " . strtolower(get_class($entity_link)) . ".id = " . $this->table . "." . strtolower(get_class($entity_link)) . "_id";
            }
        }
        return $this;
    }

    /**
     * Set the columns to be selected.
     *
     * @example name, description, category
     * @param  array|mixed  $columns
     * @return $this
     */
    public function select($columns = '*', $object = null, $defaultjoin = true) {
        $this->defaultjoin = "";
        if (is_object($columns)):
            $this->instanciateVariable($columns);
            $columns = "*";
        elseif (is_bool($object)):
            $defaultjoin = $object;
        elseif (is_object($object)):
            $this->instanciateVariable($object);
        endif;

        $this->columns = $columns;
        $this->query = " ";

        if ($defaultjoin):
            $this->join();
        endif;

        return $this;
    }

    public function delete() {
        $this->columns = null;

        $this->query = "  delete from `" . $this->table . "` ";
        $this->endquery = " where id = " . $this->instanceid;

        return $this;
    }

    /**
     * 
     * @param type $arrayvalues
     * @param type $seton
     * @param type $case
     * @return $this
     */
    public function update($arrayvalues = null, $seton = null, $case = null) {
        $this->join();
        $this->columns = null;
        $this->query = "  update `" . $this->table . "` " . $this->defaultjoin;

        if ($arrayvalues)
            return $this->set($arrayvalues, $seton, $case);

        return $this;
    }

    public function set($arrayvalues, $seton = null, $case = null) {
        $this->query .= " set ";

        // update a column on multiple rows
        if (is_array($case)) {

            $whens = [];
            $this->query .= " `" . $arrayvalues . "` = CASE " . $seton . " ";

            foreach ($case as $when => $then) {
                $whens[] = $when;
                $this->parameters[] = $then;
                $this->query .= " WHEN '$when' THEN ? ";
            }

            $this->query .= " ELSE  $seton END ";

            $whens = implode("', '", $whens);
            $this->endquery = " WHERE `" . $arrayvalues . "`  IN('" . $whens . "'); ";
        }
        // update one column on one row
        elseif ($arrayvalues && $seton != null) {
        //elseif (true) {
            $this->parameters[] = $seton;
            $this->query .= " $arrayvalues = ? ";
            $this->endquery = " WHERE " . $this->table . ".id = " . $this->instanceid;
        }
        // update multiple column on one row
        else {
            $arrayset = [];
            foreach ($arrayvalues as $key => $value) {
                if (is_object($value)) {
                    if (strtolower(get_class($value)) == "datetime") {
                        $date = array_values((array) $value);
                        $this->parameters[] = $date[0];
                        $arrayset[] = " `$key` = ? ";
                    } else {

                        $this->parameters[] = $value->getId();
                        $arrayset[] = strtolower(get_class($value)) . "_id = ? ";
                    }
                } else {

                    $this->parameters[] = $value;
                    $arrayset[] = " `$key` = ? ";
                }
            }
            $this->query .= implode(", ", $arrayset);
            $this->endquery = " WHERE " . $this->table . ".id = " . $this->instanceid;
        }

        return $this;
    }

    /**
     * Set the columns to be selected.
     *
     * @param  array|mixed  $columns
     * @return $this
     */
    public function selectcount($object = null) {
        $columns = "COUNT(*)";
        if ($object):
            $this->instanciateVariable($object);
        elseif (is_object($columns)):
            $this->instanciateVariable($columns);
            $columns = "COUNT(*)";
        endif;

        $this->columns = "COUNT(*)";
        $this->columnscount = "COUNT(*)";
//        $this->columns = is_array($columns) ? $columns : func_get_args();
        $this->query = " ";
//        $this->_selectcount = " select $columns from `". $this->table . "` ";
        $this->initdefaultjoin();

        return $this;
    }

    /**
     * init innerjoin of the $classname, base on the $classnameon. if the $classnameon is not specified, it will be set as the current 
     * class
     * @param type $classname
     * @param type $classnameon
     * @return $this
     */
    public function innerjoin($classname, $classnameon = "") {
        $this->join = strtolower(get_class($classname));
        
        if(!$classnameon)
            $classnameon = $this->objectName;
        
        $this->query .= " inner join `" . $this->join . "` on " . $this->join . ".id = " . strtolower($classnameon) . "." . $this->join . "_id";
//        $this->query .= " inner join `" . $this->join . "` ";

        return $this;
    }

    /**
     * init leftjoin of the $classname, base on the $classnameon. if the $classnameon is not specified, it will be set as the current 
     * class
     * @param type $classname
     * @param type $classnameon
     * @return $this
     */
    public function leftjoin($classname, $classnameon = "") {
        $this->join = strtolower($classname);
        
        if(!$classnameon)
            $classnameon = $this->objectName;
        
        // on ".strtolower(get_class($entity)).".id = ".strtolower(get_class($entity_owner)).".".strtolower(get_class($entity))."_id
        $this->query .= " left join `" . $this->join . "` on " . $this->join . ".id = " . strtolower($classnameon) . "." . $this->join . "_id";
//        $this->query .= " left join `" . $this->join . "` ";

        return $this;
    }

    public function on($entity) {
        //" left join `".strtolower(get_class($entity)).
        $this->query .= " on " . $this->join . ".id = " . strtolower(get_class($entity)) . "." . $this->join . "_id";

        return $this;
    }

    /**
     * Add a basic where clause to the query.
     *
     * @param  string|array|\Closure  $column
     * @param  string|null  $operator
     * @param  mixed   $value
     * @return $this
     */
    public function where($column, $operator = null, $value = null, $link = "where") {
        $this->endquery = "";
//        if(is_array($critere)){
//            
//        }
//        $this->columns = is_array($columns) ? $columns : func_get_args();

        if (is_object($column)) {

            if ($this->defaultjoinsetted) {
                $this->query .= " " . $link . " " . strtolower(get_class($column)) . '.id';
            } else {
                $this->query .= " " . $link . " " . strtolower(get_class($column)) . '_id';                
            }

            if ($column->getId()) {
                if($operator == "not"){
                    $this->query .= " != ? ";
                }else{
                    $this->query .= " = ? ";
                }
                $this->parameters[] = $column->getId();
            } else {
                $this->query .= " is null ";
            }
        }elseif (is_array($column)) {
            if(is_array($operator)){
                for ($index = 0; $index < count($column); $index++){
                    $this->andwhere($column[$index], "=", $operator[$index]);
                }
            }elseif (is_array($column[0])){
                foreach ($column as $value){
                    $this->andwhere($value[0], $value[1], $value[2]);
                }
            }else{
                foreach ($column as $key => $value){
                    $this->andwhere($key, "=", $value);
                }
            }
        } else {
            $this->query .= " " . $link . " " . $column;
            if ($operator) {
                if (in_array($operator, $this->operators)) {
                    $this->query .= " " . $operator . " ? ";
                    $this->parameters[] = $value;
                } elseif (strtolower($operator) == "like") {
                    $this->query .= " LIKE '%" . $operator . "%' ";
                } else {
                    $this->query .= " = ? ";
                    $this->parameters[] = $operator;
                }
            }
        }

        $this->initwhereclause = true;

        return $this;
    }

    public function andwhere($column, $sign = null, $value = null) {
        if ($this->initwhereclause)
            return $this->where($column, $sign, $value, 'and');
        else
            return $this->where($column, $sign, $value, 'where');
    }

    public function orwhere($column, $sign = null, $value = null) {
//        return $this->where($column, $sign, $value, 'or');
        if ($this->initwhereclause)
            return $this->where($column, $sign, $value, 'or');
        else
            return $this->where($column, $sign, $value, 'where');
    }

    /**
     * 
     * @param String|Array $values
     * @return $this
     */
    public function in($values) {
        if (is_array($values))
            $this->query .= " in (" . implode(",", $values) . ")";
        else
            $this->query .= " in ( $values )";

        return $this;
    }

    public function notin($values) {
        if (is_array($values))
            $this->query .= " not in (" . implode(",", $values) . ")";
        else
            $this->query .= " not in ( $values )";

        return $this;
    }

    public function like($value) {
//        if (is_array($values))
//            $this->query .= " LIKE '%" . implode(",", $values) . "%'";
//        else
        $this->query .= " LIKE '%" . $value . "%' ";

        return $this;
    }

    public function _like($value) {
        $this->query .= " LIKE '%" . $value . "' ";
        return $this;
    }

    public function like_($value) {
        $this->query .= " LIKE '" . $value . "%' ";
        return $this;
    }

    public function groupby($critere) {
        $this->query .= " group by " . $critere;
        return $this;
    }

    public function between($value1, $value2) {
        $this->query .= " BETWEEN '" . $value1 . "' AND '". $value2."'";
        return $this;
    }

    public function orderby($critere) {
        $this->query .= " order by " . $critere;
        return $this;
    }

    public function limit($start = 1, $max = null) {
        if ($max)
            $this->query .= " limit " . $start . ", " . $max;
        else
            $this->query .= " limit " . $start;

        return $this;
    }

    private function initquery($columns) {
        return " select " . $columns . " from `" . $this->table . "` ";
    }

    protected function querysanitize($sql) {
        return str_replace("this.", $this->table.".", $sql);
    }

    /**
     * @param $column
     * @return $this
     */
    public static function sum($column, $as = "") {
        if($as)
            $as = "as ".$as;

        return " SUM(" . $column . ") $as ";
    }

    public static function avg($column, $as = "") {
        if($as)
            $as = "as ".$as;

        return " AVG(" . $column . ") $as ";
    }

    public static function distinct($column) {
        return " DISTINCT " . $column . " ";
    }

    public function getSqlQuery() {
        if ($this->columns)
            return $this->querysanitize($this->initquery($this->columns) . $this->defaultjoin . $this->query . $this->endquery);
        else
            return $this->querysanitize($this->query . $this->endquery);
    }

    public function exec($action = 0) {
        if(in_array($action, [DBAL::$FETCH, DBAL::$FETCHALL]))
            return $this->executeDbal($this->querysanitize($this->initquery($this->columns) . $this->defaultjoin . $this->query), $this->parameters, $action);

        return $this->executeDbal($this->querysanitize($this->query . $this->endquery), $this->parameters, $action);
    }

    public function __getFirst($recursif = true) {
        return $this->limit(1)->__getOne($recursif);
    }

    public function __getLast($recursif = true) {
        return $this->orderby($this->table.".id desc")->limit(1)->__getOne($recursif);
    }

    public function __getIndex($index, $recursif = true) {
        $i = (int) $index;
        return $this->limit($i - 1, $i)->__getOne($recursif);
    }

    public function __getAllRow($setdefaultjoin = false) {
        if($setdefaultjoin)
            return $this->__findAllRow($this->querysanitize($this->initquery($this->columns) . $this->defaultjoin . $this->query), $this->parameters);

        return $this->__findAllRow($this->querysanitize($this->initquery($this->columns) . $this->query), $this->parameters);
    }

    public function __getAll($recursif = true) {
        return $this->__findAll($this->querysanitize($this->initquery($this->columns) . $this->defaultjoin . $this->query), $this->parameters, false, $recursif);
    }

    public function __getOneRow() {
        return $this->__findOneRow($this->querysanitize($this->initquery($this->columns) . $this->query), $this->parameters);
    }

    public function __getOne($recursif = true) {
        return $this->__findOne($this->querysanitize($this->initquery($this->columns) . $this->defaultjoin . $this->query), $this->parameters, false, $recursif);
    }

    public function __countEl($recursif = true) {
        return $this->__count($this->querysanitize($this->initquery($this->columnscount) . $this->defaultjoin . $this->query), $this->parameters, false, $recursif);
    }

}
