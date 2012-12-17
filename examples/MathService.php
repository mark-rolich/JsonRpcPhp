<?php
class MathService
{
    public function sum($augend, $addend1, $addend2)
    {
        return $augend + $addend1 + $addend2;
    }

    public function subtract($minuend, $subtrahend)
    {
        return $minuend - $subtrahend;
    }

    public function multiply($multiplicand, $multiplier)
    {
        return $multiplicand*$multiplier;
    }

    public function divide($dividend, $divisor)
    {
        if ($divisor === 0) {
            throw new JsonRpcApplicationException('Division by zero', 100);
        }

        return $dividend/$divisor;
    }
}
?>