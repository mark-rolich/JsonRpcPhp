<?php
class OptionalParamsService
{
    public function testNamedAllOptional($param1 = '', $param2 = null, $param3 = 100)
    {
        return array($param1, $param2, $param3);
    }
    
    public function testNamedFirstRequired($param1, $param2 = null, $param3 = 100)
    {
        return array($param1, $param2, $param3);
    }    
    
    public function testNamedThirdOptional($param1, $param2, $param3 = 100)
    {
        return array($param1, $param2, $param3);
    }    

    public function testPositionalAllOptional($param1 = '', $param2 = null, $param3 = 100)
    {
        return array($param1, $param2, $param3);
    }
}
?>