<?php
class FindMyHint {
    public $dependency;
    
    function __construct(MyHint $dependency) {
        $this->dependency = $dependency;
    }
}
?>