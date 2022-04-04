<?php

/* home.html.twig */
class __TwigTemplate_b63212fd374db0ef5396d393f21f6fd8 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<!DOCTYPE html>
<html lang=\"fr\">
<head>
    <title>Théo 1</title>

    <link rel=\"stylesheet\" href=\"";
        // line 6
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["f3"]) ? $context["f3"] : null), "path"), "html", null, true);
        echo "ui/imi/css/animate.css\" type=\"text/css\"/>
    <link rel=\"stylesheet\" href=\"";
        // line 7
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["f3"]) ? $context["f3"] : null), "path"), "html", null, true);
        echo "project/example/css/theo.css\" type=\"text/css\"/>
</head>

<body>

<div >
    <h1 >in the Air!</h1>
</div>

</body>
</html>";
    }

    public function getTemplateName()
    {
        return "home.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  28 => 7,  24 => 6,  17 => 1,);
    }
}
