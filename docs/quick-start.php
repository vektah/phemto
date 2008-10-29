<html>
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Phemto: Quick Start Guide</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <meta name="author" content="Marcus Baker">
    <meta name="description" content="phemto is a dependency injection container for php5">
    <meta name="keywords" content="php dependency injection container inversion of control servicelocator registry">
</head>

<body>

<div id="container">

<div id="top">
<h1 style="">Phemto Docs</h1>
<span>&nbsp;</span>
<span style="margin-left:5%;">
<a class="navbar" href="index.php">about the project</a>
<a class="navbar" href="quick-start.php">docs</a>
<a class="navbar" href="support.php">contact & support</a>
<a class="navbar" href="download.php">download</a>
</span>
</div>

<div id="leftnav">
</div>

<div id="rightnav">
</div>

<div id="content">

<h2>Tests</h2>

<p>For the test-infected, the <a href="http://phemto.svn.sourceforge.net/viewvc/phemto/trunk/tests/">phemto test suite</a> documents behaviour in detail.</p>

<h2>Quick Start</h2>

<p>Note that Phemto only supports constructor injection.</p>

<p>Type hints are used to figure out how to fill dependencies. When asked to instantiate a class like this:</p>
<div class="code">
<?php
$code = '<?php    

class Foo {
    function __construct(Bar $bar) {
        ...
    }
    ...
}
';
highlight_string($code);
?>
</div>

<p>Phemto identifies the "Bar" hint then searches its internal registry for a concrete implementation. Anything "greater than or equal to" Bar in the class hierarchy will do. If Bar is an interface any class which implements Bar is a viable candidate. If Bar is a class, ordinary or abstract, anything with Bar as an ancestor can be used including, of course, Bar itself (although that rather defeats the purpose). Usually you'll want to type hint to interfaces.</p>


<h3>Configuration Stage</h3>

<p>Phemto needs to be told about all the classes which you want to create as well as their dependencies, and the dependencies of dependencies etc etc. Pull out a configuration file to some easy-to-find location.</p>

<p>Suppose Bar is implemented by three classes: Quark, Strangeness and Charm:</p>

<div class="code">
<?php
$code = '<?php    

interface Bar {
    ...
    ...
}
class Quark implements Bar {
    ...
    ...
}
class Strangeness implements Bar {
    ...
    ...
}
class Charm implements Bar {
    ...
    ...
}

';
highlight_string($code);
?>
</div>

<p>Register Foo and select Charm:</p>

<div class="code">
<?php
$code = '<?php    

$phemto->register(\'Foo\');
$phemto->register(\'Charm\');

';
highlight_string($code);
?>
</div>

<p>You can save some typing in a long list by writing it out like this (fluent interface):</p>

<div class="code">
<?php
$code = '<?php    

$phemto
    ->register(\'Foo\')
    ->register(\'Charm\')
    ...
    ...
    ...
;

';
highlight_string($code);
?>
</div>

<p>For a singleton lifecycle:</p>

<div class="code">
<?php
$code = '<?php    

$phemto->register(new Singleton(\'Charm\'));

';
highlight_string($code);
?>
</div>

<p>Any object can be singleton-ised. No changes are required to your own classes.</p>
<p>Note that objects are singletons with respect to their parent phemto instance not the script as a whole. If you have two or more containers on the go, each having registered a singleton Foo, they both hold a single instance of Foo but a different Foo per container.</p>

<h3>Client Code</h3>

<p>In the application code, pull objects out of the container with an instantiate call. If you forget to register a needed dependency an exception will be thrown here.</p>

<div class="code">
<?php
$code = '<?php    

$foo = $phemto->instantiate(\'Foo\');

';
highlight_string($code);
?>
</div>

<p>Phemto wires up the Foo object and its cascade of dependencies behind the scenes. An injector represents the object graph (or a part of it) and here a node is being pulled out to do some work.</p>

<p>Note how easy it is to tweak the application behaviour simply by registering a different class: Quark, Strangeness or Charm. This is why you'd set things up to use an injector. </p>

<p>A secondary benefit is that there is less work to do in wiring up the object graph. It's all done automatically by the container. All you have to do is make a few register() calls and include the classes.</p>

<h3>Scalar Parameters</h3>

<p>Phemto will automatically deal with any type-hinted object parameters. Scalars should be passed in a separate array.</p>
<ul>
<li>Parameters should be listed in the array in the same order as they appear in the constructor.</li>
<li>Singletons' parameters are passed at registration.</li>
<li>For non-Singletons, pass parameters at instantiation.</li>
</ul>
<div class="code">
<?php
$code = '<?php    

class Foo {

    function __construct($a, Bar $bar, $b) {
    }
}

// singletons:
$phemto->register(new Singleton(\'Foo\', array($a, $b)));

// non-singletons:
$phemto->instantiate(\'Foo\', array($a, $b));

';
highlight_string($code);
?>
</div>

<p>Although scalar vars can be passed to the requested object (Foo) in an instantiate() call, Phemto currently has no means of passing vars to any of its <em>dependencies</em> at instantiation. Consider:</p>

<div class="code">
<?php
$code = '<?php    

class Foo {

    function __construct(Bar $bar) {
    }
}
class Charm implements Bar {

    function __construct($a, $b) {
    }
}

';
highlight_string($code);
?>
</div>

<p>The only way round this is to register Charm as a singleton so that vars can be passed at registration:</p>

<div class="code">
<?php
$code = '<?php    

$phemto->register(\'Foo\');
$phemto->register(new Singleton(\'Charm\', array($a, $b)));

';
highlight_string($code);
?>
</div>

<p>Note that this only applies to un-hinted parameters: type-hinted object parameters are always automatically filled by Phemto.</p>

</div>

<div id="footer">
<p style="margin-left: 100px;"><a href="http://sourceforge.net"><img src="http://sflogo.sourceforge.net/sflogo.php?group_id=199241&amp;type=5" width="210" height="62" border="0" alt="SourceForge.net Logo" /></a></p>
</div>

</div>
</body>
</html>