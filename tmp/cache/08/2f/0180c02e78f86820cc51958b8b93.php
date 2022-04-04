<?php

/* authentification/authentification.html.twig */
class __TwigTemplate_082f0180c02e78f86820cc51958b8b93 extends Twig_Template
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
<html lang=\"en\">
<head>
    ";
        // line 4
        $this->env->loadTemplate("header.html.twig")->display($context);
        // line 5
        echo "    <link rel=\"stylesheet\" href=\"/ui/css/animate/animate.css\" type=\"text/css\" />

</head>
<body>
    <div class=\"container\">

        ";
        // line 11
        if ((twig_length_filter($this->env, (isset($context["userId"]) ? $context["userId"] : null)) > 0)) {
            // line 12
            echo "        <p>loggé sur le userId ";
            echo twig_escape_filter($this->env, (isset($context["userId"]) ? $context["userId"] : null), "html", null, true);
            echo "</p>

            <a class=\"button button-danger button-rounded margin-top-s\" href=\"authentification/logout\">deconnection</a>
            <a class=\"button button-success button-rounded margin-top-s\" href=\"/\">retour sur la home</a>
        ";
        }
        // line 17
        echo "        ";
        if ((twig_length_filter($this->env, (isset($context["userId"]) ? $context["userId"] : null)) == 0)) {
            // line 18
            echo "        <h1>register</h1>
        <form method=\"post\" action=\"authentification/register\">
            <div class=\"form-group\">
                <label for=\"registerEmail\">email</label>
                <input class=\"form-control\"  id=\"registerEmail\" type=\"text\" name=\"email\" placeholder=\"email\" value=\"\">
                <label for=\"registerPassword\">mot de passe</label>
                <input class=\"form-control\" type=\"text\" name=\"password\" id=\"registerPassword\" placeholder=\"mot de passe\" value=\"\">
                <input type=\"hidden\" name=\"n\" value=\"/authentification\">
                </div>
            <button type=\"submit\" class=\"btn btn-primary\">Valider</button>
        </form>
        <h1>login</h1>
        <form method=\"post\" action=\"authentification/login\">
            <div class=\"form-group\">
                <label for=\"loginEmail\">email</label>
                <input class=\"form-control\"  id=\"loginEmail\" type=\"text\" name=\"email\" placeholder=\"email\" value=\"\">
                <label for=\"loginPassword\">mot de passe</label>
                <input class=\"form-control\" type=\"text\" name=\"password\" id=\"loginPassword\" placeholder=\"mot de passe\" value=\"\">
                <input type=\"hidden\" name=\"n\" value=\"/authentification\">
            </div>
            <button type=\"submit\" class=\"btn btn-primary\">Valider</button>
        </form>
        <h1>retrievePassword</h1>
        <form method=\"post\" action=\"authentification/lostPassword\">
            <div class=\"form-group\">
                <label for=\"lostPasswordEmail\">email</label>
                <input class=\"form-control\"  id=\"lostPasswordEmail\" type=\"text\" name=\"email\" placeholder=\"email\" value=\"\">
            </div>
            <button type=\"submit\" class=\"btn btn-primary\">Valider</button>
        </form>
        ";
        }
        // line 49
        echo "        <a href=\"/\" class=\"btn btn-success mt-3\">retour &agrave; la home</a>
    </div>



    <div class=\"modal fade\" id=\"changePasswordModal\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"myModalLabel\">
        <div class=\"modal-dialog\" role=\"document\">
            <div class=\"modal-content\">

                <div class=\"modal-header\">
                    <h4 class=\"modal-title\" id=\"myModalLabel\">Merci de saisir votre <strong>nouveau mot de passe</strong></h4>
                    <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">&times;</button>
                </div>

                <div class=\"modal-body modal-body-auth\">
                    <div class=\"form-group\">
                        <label for=\"password\" class=\"password-label\">Mot de passe</label>
                        <input type=\"password\" class=\"form-control\" id=\"changePasswordModalPassword\" placeholder=\"Mot de passe\">
                    </div>
                    <div class=\"form-group\">
                        <label for=\"passwordVerification\" class=\"password-label\">Confimer</label>
                        <input type=\"password\" class=\"form-control\" id=\"changePasswordModalPasswordVerification\" placeholder=\"Mot de passe\">
                    </div>
                    <div class=\"form-failure full-width text-align-center\">
                        <i class=\"material-icons text1000 danger-color\">thumb_down</i>
                    </div>
                    <div class=\"form-success full-width text-align-center\">
                        <i class=\"material-icons text1000 success-color\">thumb_up</i>
                    </div>
                </div>

                <div class=\"modal-footer text-align-right\">
                    <div class=\"container padding-no\">
                        <div class=\"row justify-content-end\">
                            <div class=\"col-4 col-auto hidden-xs\">
                                <button type=\"button\" class=\"btn btn-outline-dark btn-block\" data-dismiss=\"modal\">Annuler</button>
                            </div>
                            <div class=\"col-4\">
                                <a id=\"changePasswordSubmit\" class=\"btn btn-success btn-block\">Valider</a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

";
        // line 97
        $this->env->loadTemplate("footer.html.twig")->display($context);
        // line 98
        echo "
</body>
</html>";
    }

    public function getTemplateName()
    {
        return "authentification/authentification.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  131 => 98,  129 => 97,  79 => 49,  46 => 18,  43 => 17,  34 => 12,  32 => 11,  24 => 5,  22 => 4,  17 => 1,);
    }
}
