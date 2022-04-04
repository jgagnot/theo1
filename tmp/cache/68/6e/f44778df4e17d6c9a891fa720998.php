<?php

/* 404.html.twig */
class __TwigTemplate_686ef44778df4e17d6c9a891fa720998 extends Twig_Template
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
    ";
        // line 4
        $this->env->loadTemplate("header.html.twig")->display($context);
        // line 5
        echo "</head>

<body>

<div class=\"container\" id=\"container\">
    <h1 class=\"mt-6\">404 - Vous êtes perdu ?</h1>

    <div class=\"row\">
        <div class=\"col-12\">
            <a class=\"btn btn-outline-info\" href=\"/\">Retour au site</a>
        </div>
    </div>
</div>

";
        // line 19
        $this->env->loadTemplate("footer.html.twig")->display($context);
        // line 20
        echo "
</body>
</html>
";
    }

    public function getTemplateName()
    {
        return "404.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  42 => 20,  40 => 19,  24 => 5,  22 => 4,  17 => 1,);
    }
}
