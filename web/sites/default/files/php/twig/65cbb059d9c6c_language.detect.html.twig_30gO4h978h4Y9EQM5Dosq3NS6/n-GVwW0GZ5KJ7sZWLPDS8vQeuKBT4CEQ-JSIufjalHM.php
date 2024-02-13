<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* @help_topics/language.detect.html.twig */
class __TwigTemplate_b41ec9548a138841b5fe47a502fc1ae6 extends Template
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
        $this->sandbox = $this->env->getExtension('\Twig\Extension\SandboxExtension');
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 7
        ob_start(function () { return ''; });
        echo t("Detection and selection", array());
        $context["detection_text"] = ('' === $tmp = ob_get_clean()) ? '' : new Markup($tmp, $this->env->getCharset());
        // line 8
        $context["detection_link"] = $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\help\HelpTwigExtension']->getRouteLink($this->sandbox->ensureToStringAllowed(($context["detection_text"] ?? null), 8, $this->source), "language.negotiation"));
        // line 9
        echo "<h2>";
        echo t("Goal", array());
        echo "</h2>
<p>";
        // line 10
        echo t("Configure the methods used to decide which language will be used to display text on your site.", array());
        echo "</p>
<h2>";
        // line 11
        echo t("What is a language detection method?", array());
        echo "</h2>
<p>";
        // line 12
        echo t("A language detection method is a way for your site to decide what language should be used to display text. This decision is made by evaluating a series of detection methods for languages; the first detection method that gets a result will determine which language is used. If you have more than one language on your site, you can configure the detection methods that are used and the order they are evaluated, for both user interface text and content text. Configuration text always uses the interface text method, and most sites use the same detection methods for all types of text.", array());
        echo "</p>
<h2>";
        // line 13
        echo t("What language detection methods are available?", array());
        echo "</h2>
<p>";
        // line 14
        echo t("The available detection methods depend on what modules you have installed. Some of the most commonly-used language detection methods are:", array());
        echo "</p>
<dl>
  <dt>";
        // line 16
        echo t("URL", array());
        echo "</dt>
  <dd>";
        // line 17
        echo t("Use the language from the URL, which can either be in a path prefix like <em>example.com/fr/page_path</em> for French (fr) language, or in a domain like <em>fr.example.com/page_path</em>.", array());
        echo "</dd>
  <dt>";
        // line 18
        echo t("User", array());
        echo "</dt>
  <dd>";
        // line 19
        echo t("For logged in users, use the language they have set in their profiles.", array());
        echo "</dd>
  <dt>";
        // line 20
        echo t("Browser", array());
        echo "</dt>
  <dd>";
        // line 21
        echo t("Use the language preferences from the user's browser.", array());
        echo "</dd>
  <dt>";
        // line 22
        echo t("Selected language", array());
        echo "</dt>
  <dd>";
        // line 23
        echo t("Use the language most recently selected from a <em>Language switcher</em> block.", array());
        echo "</dd>
  <dt>";
        // line 24
        echo t("Session", array());
        echo "</dt>
  <dd>";
        // line 25
        echo t("Use the language from a session parameter or a request parameter; for example, adding <em>?language=fr</em> to the end of the URL.", array());
        echo "</dd>
</dl>
<h2>";
        // line 27
        echo t("Steps", array());
        echo "</h2>
<ol>
    <li>";
        // line 29
        echo t("In the <em>Manage</em> administrative menu, navigate to <em>Configuration</em> &gt; <em>Region and language</em> &gt; <em>Languages</em> &gt; @detection_link.", array("@detection_link" => ($context["detection_link"] ?? null), ));
        echo "</li>
    <li>";
        // line 30
        echo t("Check the boxes to enable the desired language detection methods, and uncheck boxes for the methods you do not want to use.", array());
        echo "</li>
    <li>";
        // line 31
        echo t("Drag the methods to change their order, if desired.", array());
        echo "</li>
    <li>";
        // line 32
        echo t("Click <em>Save settings</em>.", array());
        echo "</li>
    <li>";
        // line 33
        echo t("Click <em>Configure</em> on each enabled detection method and verify that its configuration is correct (or update the configuration if not).", array());
        echo "</li>
</ol>";
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName()
    {
        return "@help_topics/language.detect.html.twig";
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
        return array (  133 => 33,  129 => 32,  125 => 31,  121 => 30,  117 => 29,  112 => 27,  107 => 25,  103 => 24,  99 => 23,  95 => 22,  91 => 21,  87 => 20,  83 => 19,  79 => 18,  75 => 17,  71 => 16,  66 => 14,  62 => 13,  58 => 12,  54 => 11,  50 => 10,  45 => 9,  43 => 8,  39 => 7,);
    }

    public function getSourceContext()
    {
        return new Source("", "@help_topics/language.detect.html.twig", "/var/www/html/web/core/modules/language/help_topics/language.detect.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = array("set" => 7, "trans" => 7);
        static $filters = array("escape" => 29);
        static $functions = array("render_var" => 8, "help_route_link" => 8);

        try {
            $this->sandbox->checkSecurity(
                ['set', 'trans'],
                ['escape'],
                ['render_var', 'help_route_link']
            );
        } catch (SecurityError $e) {
            $e->setSourceContext($this->source);

            if ($e instanceof SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

    }
}
