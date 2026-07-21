<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* database/structure/drop_form.twig */
class __TwigTemplate_d93e11d8a060e8c7dbec5c9e82a7a7e2 extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        yield "<form action=\"";
        yield PhpMyAdmin\Url::getFromRoute("/database/structure/drop-table");
        yield "\" method=\"post\">
  ";
        // line 2
        yield PhpMyAdmin\Url::getHiddenInputs(($context["url_params"] ?? null));
        yield "

  <fieldset class=\"pma-fieldset confirmation\">
    <legend>
      ";
yield _gettext("Do you really want to execute the following query?");
        // line 7
        yield "    </legend>

    <code>";
        // line 9
        yield ($context["full_query"] ?? null);
        yield "</code>
  </fieldset>

  <fieldset class=\"pma-fieldset tblFooters\">
    <div id=\"foreignkeychk\" class=\"float-start\">
      <input type=\"hidden\" name=\"fk_checks\" value=\"0\">
      <input type=\"checkbox\" name=\"fk_checks\" id=\"fk_checks\" value=\"1\"";
        // line 15
        yield ((($context["is_foreign_key_check"] ?? null)) ? (" checked") : (""));
        yield ">
      <label for=\"fk_checks\">";
yield _gettext("Enable foreign key checks");
        // line 16
        yield "</label>
    </div>
    <div class=\"float-end\">
      <input id=\"buttonYes\" class=\"btn btn-secondary\" type=\"submit\" name=\"mult_btn\" value=\"";
yield _gettext("Yes");
        // line 19
        yield "\">
      <input id=\"buttonNo\" class=\"btn btn-secondary\" type=\"submit\" name=\"mult_btn\" value=\"";
yield _gettext("No");
        // line 20
        yield "\">
    </div>
  </fieldset>
</form>
";
        return; yield '';
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName()
    {
        return "database/structure/drop_form.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable()
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo()
    {
        return array (  79 => 20,  75 => 19,  69 => 16,  64 => 15,  55 => 9,  51 => 7,  43 => 2,  38 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "database/structure/drop_form.twig", "/Users/user/Projects/Web/start/phpmyadmin/templates/database/structure/drop_form.twig");
    }
}
