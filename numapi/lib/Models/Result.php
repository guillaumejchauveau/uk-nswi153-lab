<?php
/*
 * Result
 */

namespace Models;

/*
 * Result
 */

class Result implements \JsonSerializable
{
    /* @var double $result */
    private $result;

    public function __construct($result)
    {
        $this->result = $result;
    }

    public function jsonSerialize()
    {
        return [
            'result' => $this->result
        ];
    }
}
