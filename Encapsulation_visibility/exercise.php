<?php

class Person {
    public string $name;
    
    private int $age;

    public function introduce($name){
        return "Hi I'm $name";
    }
    
}


$person = new Person();

echo $person->introduce('Ali') . '\n';
echo $person->name;