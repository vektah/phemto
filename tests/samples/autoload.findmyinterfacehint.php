<?php
class FindMyInterfaceHint {
    public $dependency;
    
    function __construct(MyInterfaceHint $dependency) {
        $this->dependency = $dependency;
    }
}
?>