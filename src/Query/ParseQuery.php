<?php

namespace Monga\Query;
use League\Monga\Collection;
use League\Monga\Query\Update;

/**
 * Class ParseQuery
 */
class ParseQuery
{
    const PROJECTION = 'Projections';
    const TARGET = 'Target';
    const CONDITION = 'Condition';
    const ORDER_BY_FIELD = 'Order_By_Field';
    const SKIP_RECORDS = 'Skip_Records';
    const MAX_RECORDS = 'Max_Records';
    
    private $condition;
    
    private $fieldCondition;
    private $equalCondition;

    private $orFieldCondition;
    private $orEqualCondition;
    
    private $arrayQuery;
    
    public function __construct(array $arrayQuery)
    {
        $this->arrayQuery = $arrayQuery;
        foreach ($this->arrayQuery as $key=>$item) {
            if ($key == self::CONDITION) {
                $this->condition = trim($item);   
            }
        }
    }

    /**
     * @param Collection $collection
     */
    public function parseQuery(
        Collection $collection
    ){
        if ($this->condition) {
            if (strpos($this->condition, 'or')) {
                $expOr = explode('or', $this->condition);
                foreach ($expOr as $key=>$item) {
                    $expEq = explode('=', $item);
                    $field[$key][] = trim($expEq[0]);
                    $field[$key][] = trim($expEq[1]);
                }
            }
            $this->fieldCondition = $field[0][0];
            $this->equalCondition = $field[0][1];
//            name=Frank or name=John
            $this->orFieldCondition = $field[1][0];
            $this->orEqualCondition = $field[1][1];
            
            $frank = $collection->find(function ($query) {
                /** @var Update $query */
                $query
                    ->where($this->fieldCondition, $this->equalCondition);
                $query
                    ->orWhere($this->orFieldCondition, $this->orEqualCondition);
            });


            return $frank->toArray();
        }
    }
}