<html>
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Phemto: Dependency Injection for PHP</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <meta name="author" content="Marcus Baker">
    <meta name="description" content="phemto is a dependency injection container for php5">
    <meta name="keywords" content="php dependency injection container inversion of control servicelocator registry">
</head>

<body>

<div id="container">

<div id="top">
<h1 style="">Phemto</h1>
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

<h2>What is Phemto?</h2>

<p> Phemto is a dependency injector for PHP 5. It's
the smallest dependency injector I could come up with that is
still useful in a PHP environment. Because of it's simplicity
it makes an ideal tool to learn about dependency injection or
for managing medium applications or small frameworks. If your
needs are more sophisticated, then you might want to look at
<a href="http://www.picocontainer.org/">the Pico for PHP</a>.</p>

<h2>What is Dependency Injection?</h2>

<p>Suppose object Foo needs to make calls on another object, Bar. Foo can either instantiate Bar in one of its own methods or Bar can be passed in to Foo &#8212; dependency injection.</p>

<p>The core of a dependency injector is it's internal regisitry.
This can hold single instances or factories and allow global
access. However it has several capabilities that place it
above a simple Registry pattern.</p>

<p>The first is that construction can be initiated with only an
interface name in the code that uses the instance. This
decouples this client code from dependent object construction.
This is a powerful feature for frameworks that may utilise
plug-ins from many different authors. The framework can
specify interfaces, and utillise code that uses those
interfaces, without ever coming into contact with the
implementation code. The application author determines the
wiring when the injector is set up. This also makes testing
easier, because mock implementations can be passed to
the application during testing.</p>

<p>The second capability is to automatically handle constructor
arguments by the same process. This means that implementations
can also publicise their dependencies which can in turn be
managed by the injector. As the publishing mechanism is
simple type hints in the constructor, it means that the
component implementation is not just independent of the
framework and it's own constructor dependencies, it's also
independent of the injector as well. Constructor injection
makes the implementations easy to test as mock dependencies
can simply be passed to the constructor.</p>

<p>The third capability is to detach the lifecycle of the object
from the class. The injector can register a class as a
normal object, a Singleton, a session object or any other
lifecycle choice. This decision is kept out of the original
class and also out of the client code of the instance.
This means that the class itself is not cluttered by this
code and, being a normal object, is easy to test.</p>

<p>A dependency injector achieves a remarkable degree of
decoupling.</p>

<p>Phemto has some compromises over Pico. The most significant
is that every hint has to be unique within a constructor.
Phemto has no way of determining the class by parameter
position or any other means. It simply injects every hint
for which it has an instance available. Everywhere else
gets a parameter if supplied either at registration or
instantiation.</p>

<p>Phemto is however still fairly extensible. The service
locators used to instantiate the objects can be replaced
using dependency injection themselves. In addition new
registration mechanisms can be added on the fly.</p>

<p>Phemto is public domain code. The only requirement is to
acknowledge the authors in articles where phemto is used
as an example. No warranty is implied by this. You use
this code entirely at your own risk.</p>

<p style="font-style: italic;">Marcus Baker</p>

<h2>Further Reading</h2>
<ul>
    <li><a href="http://martinfowler.com/articles/injection.html">Inversion of Control Containers and the Dependency Injection Pattern</a> <i>Martin Fowler</i></li>
    <li><a href="http://c2.com/cgi/wiki?DependencyInjection">Dependency Injection</a> <i>c2 wiki</i></li>
    <li><a href="http://en.wikipedia.org/wiki/Inversion_of_control">Inversion of Control</a> <i>WikiPedia</i></li>
    <li><a href="http://www.picocontainer.org/">Picocontainer</a></li>
</ul>

</div>

<div id="footer">
<p style="margin-left: 100px;"><a href="http://sourceforge.net"><img src="http://sflogo.sourceforge.net/sflogo.php?group_id=199241&amp;type=5" width="210" height="62" border="0" alt="SourceForge.net Logo" /></a></p>
</div>

</div>
</body>
</html>