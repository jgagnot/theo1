<?php

/* shopping/defaultStore.html.twig */
class __TwigTemplate_99913edca6334c99e75c52bfcbe0232a extends Twig_Template
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
        echo " <!DOCTYPE html>
 <html lang=\"fr\">
 <head>
     ";
        // line 4
        $this->env->loadTemplate("header.html.twig")->display($context);
        // line 5
        echo " </head>

 <body class=\"padding-l\">

 <div class=\"margin-top-m border-top padding-top-m\">Bienvenue dans la boutique n°";
        // line 9
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["store"]) ? $context["store"] : null), "id"), "html", null, true);
        echo " (defaultStore)</div>
 <div>Panier :</div>
 ";
        // line 11
        $this->env->loadTemplate("/shopping/basket.html.twig")->display($context);
        // line 12
        echo "

 ";
        // line 14
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["item_array"]) ? $context["item_array"] : null));
        foreach ($context['_seq'] as $context["key1"] => $context["value1"]) {
            // line 15
            echo "
     <div class=\"margin-top-s bold\" id=\"article\">
         ARTICLE ";
            // line 17
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["value1"]) ? $context["value1"] : null), "itemId"), "html", null, true);
            echo "
     </div>
     <div>Nom : <a href=\"";
            // line 19
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["f3"]) ? $context["f3"] : null), "path"), "html", null, true);
            echo "shop/store/";
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["store"]) ? $context["store"] : null), "id"), "html", null, true);
            echo "/";
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["value1"]) ? $context["value1"] : null), "seoUrl"), "html", null, true);
            echo "\">";
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["value1"]) ? $context["value1"] : null), "name"), "html", null, true);
            echo "</a></div>
     <div>Descriptif : ";
            // line 20
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["value1"]) ? $context["value1"] : null), "description"), "html", null, true);
            echo "</div>

     <div>References pour ce produit : </div>
     ";
            // line 23
            $context['_parent'] = (array) $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["value1"]) ? $context["value1"] : null), "reference_array"));
            foreach ($context['_seq'] as $context["_key"] => $context["reference"]) {
                // line 24
                echo "
         <div>Reference numero ";
                // line 25
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context["reference"]) ? $context["reference"] : null), "id"), "html", null, true);
                echo " :</div>
         <div>

         <div>  ";
                // line 28
                $context['_parent'] = (array) $context;
                $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["reference"]) ? $context["reference"] : null), "product_array"));
                foreach ($context['_seq'] as $context["_key"] => $context["product"]) {
                    echo twig_escape_filter($this->env, $this->getAttribute((isset($context["product"]) ? $context["product"] : null), "productQuantity"), "html", null, true);
                    echo " ";
                    echo twig_escape_filter($this->env, $this->getAttribute((isset($context["product"]) ? $context["product"] : null), "name"), "html", null, true);
                    echo " ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['product'], $context['_parent'], $context['loop']);
                $context = array_merge($_parent, array_intersect_key($context, $_parent));
                echo "</div>

         Prix :
         ";
                // line 31
                $context['_parent'] = (array) $context;
                $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["reference"]) ? $context["reference"] : null), "price_array"));
                foreach ($context['_seq'] as $context["_key"] => $context["price"]) {
                    echo " ";
                    if (($this->getAttribute((isset($context["price"]) ? $context["price"] : null), "currency") == "eur")) {
                        // line 32
                        echo "             <span>";
                        echo twig_escape_filter($this->env, twig_number_format_filter($this->env, $this->getAttribute((isset($context["price"]) ? $context["price"] : null), "price"), 2, ",", " "), "html", null, true);
                        echo " euros</span>


             </div>
             <div>TVA : ";
                        // line 36
                        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["price"]) ? $context["price"] : null), "vat"), "html", null, true);
                        echo "</div>

         ";
                    }
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['price'], $context['_parent'], $context['loop']);
                $context = array_merge($_parent, array_intersect_key($context, $_parent));
                // line 39
                echo "         <div>Stock restant: ";
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context["reference"]) ? $context["reference"] : null), "stock"), "html", null, true);
                echo "</div>
         <hr>
     ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['reference'], $context['_parent'], $context['loop']);
            $context = array_merge($_parent, array_intersect_key($context, $_parent));
            // line 42
            echo "

     <div class=\"margin-top-xxxs\">
         ";
            // line 45
            if ($this->getAttribute((isset($context["store"]) ? $context["store"] : null), "stockActived")) {
                // line 46
                echo "             <div>Stock : ";
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context["value1"]) ? $context["value1"] : null), "stockQuantity"), "html", null, true);
                echo "</div>
             <div>Reste : ";
                // line 47
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context["value1"]) ? $context["value1"] : null), "stockRefreshed"), "html", null, true);
                echo "</div>
         ";
            }
            // line 49
            echo "         ";
            if ($this->getAttribute((isset($context["value1"]) ? $context["value1"] : null), "countdown")) {
                // line 50
                echo "             ";
                echo twig_escape_filter($this->env, twig_date_format_filter($this->env, $this->getAttribute((isset($context["value1"]) ? $context["value1"] : null), "saleStop"), "Y-m-d H:i:s"), "html", null, true);
                echo "
             <div><span id=\"days";
                // line 51
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context["value1"]) ? $context["value1"] : null), "itemId"), "html", null, true);
                echo "\"></span> <span id=\"hours";
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context["value1"]) ? $context["value1"] : null), "itemId"), "html", null, true);
                echo "\"></span>:<span id=\"minutes";
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context["value1"]) ? $context["value1"] : null), "itemId"), "html", null, true);
                echo "\"></span>:<span id=\"seconds";
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context["value1"]) ? $context["value1"] : null), "itemId"), "html", null, true);
                echo "\"></span></div>
             <script>
                 \$(document).ready(function () {
                     countdown('";
                // line 54
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context["value1"]) ? $context["value1"] : null), "itemId"), "html", null, true);
                echo "',";
                echo twig_escape_filter($this->env, twig_date_format_filter($this->env, $this->getAttribute((isset($context["value1"]) ? $context["value1"] : null), "saleStop"), "Y"), "html", null, true);
                echo ",";
                echo twig_escape_filter($this->env, twig_date_format_filter($this->env, $this->getAttribute((isset($context["value1"]) ? $context["value1"] : null), "saleStop"), "m"), "html", null, true);
                echo ",";
                echo twig_escape_filter($this->env, twig_date_format_filter($this->env, $this->getAttribute((isset($context["value1"]) ? $context["value1"] : null), "saleStop"), "d"), "html", null, true);
                echo ",";
                echo twig_escape_filter($this->env, twig_date_format_filter($this->env, $this->getAttribute((isset($context["value1"]) ? $context["value1"] : null), "saleStop"), "H"), "html", null, true);
                echo ",";
                echo twig_escape_filter($this->env, twig_date_format_filter($this->env, $this->getAttribute((isset($context["value1"]) ? $context["value1"] : null), "saleStop"), "i"), "html", null, true);
                echo ",";
                echo twig_escape_filter($this->env, twig_date_format_filter($this->env, $this->getAttribute((isset($context["value1"]) ? $context["value1"] : null), "saleStop"), "s"), "html", null, true);
                echo ");
                 });
             </script>
         ";
            }
            // line 58
            echo "     </div>

 ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['key1'], $context['value1'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
        // line 61
        echo "
 <script type=\"text/javascript\" src=\"";
        // line 62
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["f3"]) ? $context["f3"] : null), "path"), "html", null, true);
        echo "ui/js/imi/shopping.js\"></script>

 <script>

     \$('.btnWishList').click (function () {
         addWishList('";
        // line 67
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["f3"]) ? $context["f3"] : null), "path"), "html", null, true);
        echo "',\$(this).attr('id'),\$(this).attr('id').substring(8,\$(this).attr('id').length),";
        echo twig_escape_filter($this->env, (isset($context["userId"]) ? $context["userId"] : null), "html", null, true);
        echo ");
     });

 </script>

";
        // line 72
        $this->env->loadTemplate("footer.html.twig")->display($context);
    }

    public function getTemplateName()
    {
        return "shopping/defaultStore.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  218 => 72,  208 => 67,  200 => 62,  197 => 61,  189 => 58,  170 => 54,  158 => 51,  153 => 50,  150 => 49,  145 => 47,  140 => 46,  138 => 45,  133 => 42,  123 => 39,  113 => 36,  105 => 32,  99 => 31,  83 => 28,  77 => 25,  74 => 24,  70 => 23,  64 => 20,  54 => 19,  49 => 17,  45 => 15,  41 => 14,  37 => 12,  35 => 11,  30 => 9,  24 => 5,  22 => 4,  17 => 1,);
    }
}
