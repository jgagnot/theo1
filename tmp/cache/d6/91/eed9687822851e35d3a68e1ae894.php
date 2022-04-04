<?php

/* /shopping/basket.html.twig */
class __TwigTemplate_d691eed9687822851e35d3a68e1ae894 extends Twig_Template
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
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["basket_array"]) ? $context["basket_array"] : null), "itemList"));
        foreach ($context['_seq'] as $context["key"] => $context["value"]) {
            // line 2
            echo "    ";
            if (($this->getAttribute((isset($context["value"]) ? $context["value"] : null), "itemId") != "")) {
                // line 3
                echo "        <div>
            ";
                // line 4
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context["value"]) ? $context["value"] : null), "name"), "html", null, true);
                echo "
            <span class=\"";
                // line 5
                if (($this->getAttribute((isset($context["value"]) ? $context["value"] : null), "couponId") != (-1))) {
                    echo "line-through";
                }
                echo "\">";
                echo twig_escape_filter($this->env, twig_number_format_filter($this->env, ($this->getAttribute((isset($context["value"]) ? $context["value"] : null), "price") / 100), 2, ",", " "), "html", null, true);
                echo "</span>
            ";
                // line 6
                if (($this->getAttribute((isset($context["value"]) ? $context["value"] : null), "couponId") != (-1))) {
                    echo twig_escape_filter($this->env, twig_number_format_filter($this->env, ($this->getAttribute((isset($context["value"]) ? $context["value"] : null), "discountPrice") / 100), 2, ",", " "), "html", null, true);
                }
                // line 7
                echo "            (";
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context["value"]) ? $context["value"] : null), "couponId"), "html", null, true);
                echo ")
            ";
                // line 8
                if ((isset($context["basketLink"]) ? $context["basketLink"] : null)) {
                    echo "<a href=\"";
                    echo twig_escape_filter($this->env, $this->getAttribute((isset($context["f3"]) ? $context["f3"] : null), "path"), "html", null, true);
                    echo "shop/eraseItem/";
                    echo twig_escape_filter($this->env, $this->getAttribute((isset($context["store"]) ? $context["store"] : null), "id"), "html", null, true);
                    echo "/";
                    echo twig_escape_filter($this->env, $this->getAttribute((isset($context["value"]) ? $context["value"] : null), "itemId"), "html", null, true);
                    echo "?cc=";
                    echo twig_escape_filter($this->env, (isset($context["couponCode"]) ? $context["couponCode"] : null), "html", null, true);
                    echo "\">SUP</a>";
                }
                // line 9
                echo "        </div>
    ";
            }
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['key'], $context['value'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
        // line 12
        echo "
<div class=\"none\">Nombre d'articles : <span class=\"danger-color bold\">";
        // line 13
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["basket_array"]) ? $context["basket_array"] : null), "length"), "html", null, true);
        echo "</span></div>
<div>
    Total :
    <span class=\"bold\">
        <span class=\"";
        // line 17
        if (($this->getAttribute($this->getAttribute((isset($context["basket_array"]) ? $context["basket_array"] : null), "receipt"), "total") != $this->getAttribute($this->getAttribute((isset($context["basket_array"]) ? $context["basket_array"] : null), "receipt"), "discountTotal"))) {
            echo "danger-color  line-through";
        }
        echo "\">";
        echo twig_escape_filter($this->env, twig_number_format_filter($this->env, ($this->getAttribute($this->getAttribute((isset($context["basket_array"]) ? $context["basket_array"] : null), "receipt"), "total") / 100), 2, ",", " "), "html", null, true);
        echo "</span>
        <span class=\"";
        // line 18
        if (($this->getAttribute($this->getAttribute((isset($context["basket_array"]) ? $context["basket_array"] : null), "receipt"), "total") != $this->getAttribute($this->getAttribute((isset($context["basket_array"]) ? $context["basket_array"] : null), "receipt"), "discountTotal"))) {
            echo "success-color";
        } else {
            echo "none";
        }
        echo "\">";
        echo twig_escape_filter($this->env, twig_number_format_filter($this->env, ($this->getAttribute($this->getAttribute((isset($context["basket_array"]) ? $context["basket_array"] : null), "receipt"), "discountTotal") / 100), 2, ",", " "), "html", null, true);
        echo "</span>
    </span>
</div>


    <div><a href=\"";
        // line 23
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["f3"]) ? $context["f3"] : null), "path"), "html", null, true);
        echo "shop/store/";
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["store"]) ? $context["store"] : null), "id"), "html", null, true);
        echo "/checkout\" class=\"btn-default btn ";
        if (($this->getAttribute((isset($context["basket_array"]) ? $context["basket_array"] : null), "length") == 0)) {
            echo "disabled";
        }
        echo "\">Valider la commande</a></div>
    <div><a href=\"";
        // line 24
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["f3"]) ? $context["f3"] : null), "path"), "html", null, true);
        echo "shop/expressCheckout/";
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["isUserExpressCheckout"]) ? $context["isUserExpressCheckout"] : null), "activedMeanPaymentId"), "html", null, true);
        echo "/";
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["isUserExpressCheckout"]) ? $context["isUserExpressCheckout"] : null), "activedUserAdressId"), "html", null, true);
        echo "\" class=\"btn-warning btn margin-left-xxs ";
        if (($this->getAttribute((isset($context["isUserExpressCheckout"]) ? $context["isUserExpressCheckout"] : null), "isUserExpressCheckout") == 0)) {
            echo "none";
        }
        echo " ";
        if (($this->getAttribute((isset($context["basket_array"]) ? $context["basket_array"] : null), "length") == 0)) {
            echo "disabled";
        }
        echo "\">Commande express</a></div>
    ";
        // line 25
        if (($this->getAttribute((isset($context["basket_array"]) ? $context["basket_array"] : null), "length") != 0)) {
            // line 26
            echo "        <div><a href=\"";
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["f3"]) ? $context["f3"] : null), "path"), "html", null, true);
            echo "shop/store/";
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["store"]) ? $context["store"] : null), "id"), "html", null, true);
            echo "/dropBasket\" class=\"btn-link margin-top-xxxs block\">Vider le panier</a></div>
    ";
        }
    }

    public function getTemplateName()
    {
        return "/shopping/basket.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  128 => 26,  126 => 25,  110 => 24,  100 => 23,  86 => 18,  78 => 17,  71 => 13,  68 => 12,  60 => 9,  48 => 8,  43 => 7,  39 => 6,  31 => 5,  27 => 4,  21 => 2,  218 => 72,  208 => 67,  200 => 62,  197 => 61,  189 => 58,  170 => 54,  158 => 51,  153 => 50,  150 => 49,  145 => 47,  140 => 46,  138 => 45,  133 => 42,  123 => 39,  113 => 36,  105 => 32,  99 => 31,  83 => 28,  77 => 25,  74 => 24,  70 => 23,  64 => 20,  54 => 19,  49 => 17,  45 => 15,  41 => 14,  37 => 12,  35 => 11,  30 => 9,  24 => 3,  22 => 4,  17 => 1,);
    }
}
