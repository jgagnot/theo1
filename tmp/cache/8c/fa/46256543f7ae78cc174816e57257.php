<?php

/* footer.html.twig */
class __TwigTemplate_8cfa46256543f7ae78cc174816e57257 extends Twig_Template
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
        echo "<!-- Notification -->
<div id=\"toast-container\" class=\"none\"></div>
<!-- Notification End-->

<!-- Express -->
<div class=\"modal fade\" id=\"expressModal\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"exampleModalLabel\" aria-hidden=\"true\">
    <div id=\"expressModalRole\" class=\"modal-dialog modal-sm\" role=\"document\">
        <div class=\"modal-content\">
            <div class=\"modal-header\">
                <h5 id=\"expressModalTitle\" class=\"modal-title bold project-first-color\"></h5>
                <button class=\"close\" type=\"button\" data-dismiss=\"modal\" aria-label=\"Close\"><span aria-hidden=\"true\">×</span></button>
            </div>
            <div class=\"modal-body\" id=\"expressModalBody\"></div>
            <div class=\"modal-footer\">
                <button class=\"btn btn-light btn-sm\" type=\"button\" data-dismiss=\"modal\">Fermer</button>
            </div>
        </div>
    </div>
</div>
<!-- Express End-->

<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src=\"https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js\"></script>
<script src=\"https://oss.maxcdn.com/respond/1.4.2/respond.min.js\"></script>
<![endif]-->

<script>

    \$(document).ready(function () {
        //on affiche le message flash (alert) éventuel
        ";
        // line 33
        if (twig_length_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["f3"]) ? $context["f3"] : null), "SESSION"), "flashContent"))) {
            // line 34
            echo "        flash('";
            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["f3"]) ? $context["f3"] : null), "SESSION"), "flashContent"), "html", null, true);
            echo "','";
            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["f3"]) ? $context["f3"] : null), "SESSION"), "flashContainer"), "html", null, true);
            echo "','";
            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["f3"]) ? $context["f3"] : null), "SESSION"), "flashType"), "html", null, true);
            echo "',";
            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["f3"]) ? $context["f3"] : null), "SESSION"), "flashTimeout"), "html", null, true);
            echo ",0,'";
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["f3"]) ? $context["f3"] : null), "path"), "html", null, true);
            echo "');
        ";
        }
        // line 36
        echo "
        //on affiche le message express (modal) éventuel
        ";
        // line 38
        if (twig_length_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["f3"]) ? $context["f3"] : null), "SESSION"), "expressTitle"))) {
            // line 39
            echo "        express('";
            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["f3"]) ? $context["f3"] : null), "SESSION"), "expressTitle"), "html", null, true);
            echo "','";
            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["f3"]) ? $context["f3"] : null), "SESSION"), "expressBody"), "html", null, true);
            echo "','";
            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["f3"]) ? $context["f3"] : null), "SESSION"), "expressWidth"), "html", null, true);
            echo "',0,";
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["f3"]) ? $context["f3"] : null), "path"), "html", null, true);
            echo ");
        ";
        }
        // line 41
        echo "
        //on affiche le message toast éventuel
        ";
        // line 43
        if (twig_length_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["f3"]) ? $context["f3"] : null), "SESSION"), "toastContent"))) {
            // line 44
            echo "        toast('";
            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["f3"]) ? $context["f3"] : null), "SESSION"), "toastContent"), "html", null, true);
            echo "','";
            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["f3"]) ? $context["f3"] : null), "SESSION"), "containerStyle"), "html", null, true);
            echo "','";
            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["f3"]) ? $context["f3"] : null), "SESSION"), "toastStyle"), "html", null, true);
            echo "','";
            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["f3"]) ? $context["f3"] : null), "SESSION"), "toastEnabledTimeout"), "html", null, true);
            echo "','";
            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["f3"]) ? $context["f3"] : null), "SESSION"), "toastDisabledTimeout"), "html", null, true);
            echo "',";
            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["f3"]) ? $context["f3"] : null), "SESSION"), "toastClickClose"), "html", null, true);
            echo ", 0,'";
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["f3"]) ? $context["f3"] : null), "path"), "html", null, true);
            echo "');
        ";
        }
        // line 46
        echo "
    });

</script>";
    }

    public function getTemplateName()
    {
        return "footer.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  109 => 46,  89 => 43,  73 => 39,  71 => 38,  53 => 34,  51 => 33,  107 => 58,  104 => 55,  99 => 53,  96 => 51,  91 => 44,  88 => 44,  85 => 41,  79 => 38,  76 => 36,  72 => 35,  67 => 36,  59 => 28,  56 => 25,  54 => 24,  52 => 23,  47 => 21,  45 => 20,  42 => 17,  30 => 10,  25 => 7,  36 => 13,  34 => 13,  24 => 5,  22 => 4,  17 => 1,);
    }
}
