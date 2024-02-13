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

/* themes/contrib/gin/templates/system/system-themes-page.html.twig */
class __TwigTemplate_00c069bd3afb0c3275a6a43a0910b16e extends Template
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
        // line 36
        echo "<div";
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["attributes"] ?? null), 36, $this->source), "html", null, true);
        echo ">
  ";
        // line 37
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["theme_groups"] ?? null));
        $context['loop'] = [
          'parent' => $context['_parent'],
          'index0' => 0,
          'index'  => 1,
          'first'  => true,
        ];
        if (is_array($context['_seq']) || (is_object($context['_seq']) && $context['_seq'] instanceof \Countable)) {
            $length = count($context['_seq']);
            $context['loop']['revindex0'] = $length - 1;
            $context['loop']['revindex'] = $length;
            $context['loop']['length'] = $length;
            $context['loop']['last'] = 1 === $length;
        }
        foreach ($context['_seq'] as $context["_key"] => $context["theme_group"]) {
            // line 38
            echo "    ";
            // line 39
            $context["theme_group_classes"] = ["system-themes-list", ("system-themes-list--" . $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source,             // line 41
$context["theme_group"], "state", [], "any", false, false, true, 41), 41, $this->source)), "clearfix", "gin-layer-wrapper"];
            // line 46
            echo "
    ";
            // line 48
            $context["card_alignment"] = (((twig_get_attribute($this->env, $this->source, $context["theme_group"], "state", [], "any", false, false, true, 48) == "installed")) ? ("horizontal") : ("vertical"));
            // line 50
            echo "
    ";
            // line 51
            if ( !twig_get_attribute($this->env, $this->source, $context["loop"], "first", [], "any", false, false, true, 51)) {
                // line 52
                echo "      <hr>
    ";
            }
            // line 54
            echo "
    <div";
            // line 55
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, $context["theme_group"], "attributes", [], "any", false, false, true, 55), "addClass", [($context["theme_group_classes"] ?? null)], "method", false, false, true, 55), 55, $this->source), "html", null, true);
            echo ">
      <h2 class=\"system-themes-list__header\">";
            // line 56
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, $context["theme_group"], "title", [], "any", false, false, true, 56), 56, $this->source), "html", null, true);
            echo "</h2>
      <div class=\"card-list ";
            // line 57
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar((((($context["card_alignment"] ?? null) == "horizontal")) ? ("card-list--two-cols") : ("card-list--four-cols")));
            echo "\">
        ";
            // line 58
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, $context["theme_group"], "themes", [], "any", false, false, true, 58));
            foreach ($context['_seq'] as $context["_key"] => $context["theme"]) {
                // line 59
                echo "          ";
                // line 60
                $context["theme_classes"] = [((twig_get_attribute($this->env, $this->source,                 // line 61
$context["theme"], "is_default", [], "any", false, false, true, 61)) ? ("theme-default") : ("")), ((twig_get_attribute($this->env, $this->source,                 // line 62
$context["theme"], "is_admin", [], "any", false, false, true, 62)) ? ("theme-admin") : ("")), "card", ("card--" . $this->sandbox->ensureToStringAllowed(                // line 64
($context["card_alignment"] ?? null), 64, $this->source)), "card-list__item"];
                // line 68
                echo "          ";
                // line 69
                $context["theme_title_classes"] = ["card__content-item", "heading-f"];
                // line 74
                echo "          ";
                // line 75
                $context["theme_description_classes"] = ["card__content-item"];
                // line 79
                echo "          <div";
                echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, $context["theme"], "attributes", [], "any", false, false, true, 79), "addClass", [($context["theme_classes"] ?? null)], "method", false, false, true, 79), "setAttribute", ["aria-labelledby", twig_get_attribute($this->env, $this->source, $context["theme"], "title_id", [], "any", false, false, true, 79)], "method", false, false, true, 79), "setAttribute", ["aria-describedby", ((twig_get_attribute($this->env, $this->source, $context["theme"], "description_id", [], "any", false, false, true, 79)) ?: (null))], "method", false, false, true, 79), 79, $this->source), "html", null, true);
                echo ">
            ";
                // line 80
                if (twig_get_attribute($this->env, $this->source, $context["theme"], "screenshot", [], "any", false, false, true, 80)) {
                    // line 81
                    echo "              <div class=\"card__image\">
                ";
                    // line 82
                    echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, $context["theme"], "screenshot", [], "any", false, false, true, 82), 82, $this->source), "html", null, true);
                    echo "
              </div>
            ";
                }
                // line 85
                echo "            <div class=\"card__content-wrapper\">
              <div class=\"card__content\">
                <h3";
                // line 87
                echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, $this->extensions['Drupal\Core\Template\TwigExtension']->createAttribute(["id" => twig_get_attribute($this->env, $this->source, $context["theme"], "title_id", [], "any", false, false, true, 87)]), "addClass", [($context["theme_title_classes"] ?? null)], "method", false, false, true, 87), 87, $this->source), "html", null, true);
                echo " id=";
                echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, $context["theme"], "title_id", [], "any", false, false, true, 87), 87, $this->source), "html", null, true);
                echo ">";
                // line 88
                echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, $context["theme"], "name", [], "any", false, false, true, 88), 88, $this->source), "html", null, true);
                echo " ";
                echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, $context["theme"], "version", [], "any", false, false, true, 88), 88, $this->source), "html", null, true);
                // line 89
                if (twig_get_attribute($this->env, $this->source, $context["theme"], "notes", [], "any", false, false, true, 89)) {
                    // line 90
                    echo "                    (";
                    echo $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\TwigExtension']->safeJoin($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, $context["theme"], "notes", [], "any", false, false, true, 90), 90, $this->source), ", "));
                    echo ")";
                }
                // line 92
                echo "</h3>

                ";
                // line 94
                if ((twig_get_attribute($this->env, $this->source, $context["theme"], "description", [], "any", false, false, true, 94) && twig_get_attribute($this->env, $this->source, $context["theme"], "description_id", [], "any", false, false, true, 94))) {
                    // line 95
                    echo "                  <div";
                    echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, $this->extensions['Drupal\Core\Template\TwigExtension']->createAttribute(["id" => ((twig_get_attribute($this->env, $this->source, $context["theme"], "description_id", [], "any", false, false, true, 95)) ?: (null))]), "addClass", [($context["theme_description_classes"] ?? null)], "method", false, false, true, 95), 95, $this->source), "html", null, true);
                    echo ">
                    ";
                    // line 96
                    echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, $context["theme"], "description", [], "any", false, false, true, 96), 96, $this->source), "html", null, true);
                    echo "
                  </div>";
                }
                // line 99
                echo "</div>

              <div class=\"card__footer\">
                ";
                // line 102
                if (twig_get_attribute($this->env, $this->source, $context["theme"], "module_dependencies", [], "any", false, false, true, 102)) {
                    // line 103
                    echo "                  <div class=\"theme-info__requires\">
                    ";
                    // line 104
                    echo $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Requires: @module_dependencies", ["@module_dependencies" => $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, $context["theme"], "module_dependencies", [], "any", false, false, true, 104), 104, $this->source))]));
                    echo "
                  </div>
                ";
                }
                // line 107
                echo "                ";
                // line 108
                echo "                ";
                if (twig_get_attribute($this->env, $this->source, $context["theme"], "incompatible", [], "any", false, false, true, 108)) {
                    // line 109
                    echo "                  <small class=\"incompatible\">";
                    echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, $context["theme"], "incompatible", [], "any", false, false, true, 109), 109, $this->source), "html", null, true);
                    echo "</small>
                ";
                } else {
                    // line 111
                    echo "                    ";
                    echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, $context["theme"], "operations", [], "any", false, false, true, 111), 111, $this->source), "html", null, true);
                    echo "
                ";
                }
                // line 113
                echo "              </div>
            </div>
          </div>
        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['theme'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 117
            echo "      </div>
    </div>
  ";
            ++$context['loop']['index0'];
            ++$context['loop']['index'];
            $context['loop']['first'] = false;
            if (isset($context['loop']['length'])) {
                --$context['loop']['revindex0'];
                --$context['loop']['revindex'];
                $context['loop']['last'] = 0 === $context['loop']['revindex0'];
            }
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['theme_group'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 120
        echo "</div>
";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["attributes", "theme_groups", "loop"]);    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName()
    {
        return "themes/contrib/gin/templates/system/system-themes-page.html.twig";
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
        return array (  224 => 120,  208 => 117,  199 => 113,  193 => 111,  187 => 109,  184 => 108,  182 => 107,  176 => 104,  173 => 103,  171 => 102,  166 => 99,  161 => 96,  156 => 95,  154 => 94,  150 => 92,  145 => 90,  143 => 89,  139 => 88,  134 => 87,  130 => 85,  124 => 82,  121 => 81,  119 => 80,  114 => 79,  112 => 75,  110 => 74,  108 => 69,  106 => 68,  104 => 64,  103 => 62,  102 => 61,  101 => 60,  99 => 59,  95 => 58,  91 => 57,  87 => 56,  83 => 55,  80 => 54,  76 => 52,  74 => 51,  71 => 50,  69 => 48,  66 => 46,  64 => 41,  63 => 39,  61 => 38,  44 => 37,  39 => 36,);
    }

    public function getSourceContext()
    {
        return new Source("", "themes/contrib/gin/templates/system/system-themes-page.html.twig", "/var/www/html/web/themes/contrib/gin/templates/system/system-themes-page.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = array("for" => 37, "set" => 39, "if" => 51);
        static $filters = array("escape" => 36, "safe_join" => 90, "t" => 104, "render" => 104);
        static $functions = array("create_attribute" => 87);

        try {
            $this->sandbox->checkSecurity(
                ['for', 'set', 'if'],
                ['escape', 'safe_join', 't', 'render'],
                ['create_attribute']
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
