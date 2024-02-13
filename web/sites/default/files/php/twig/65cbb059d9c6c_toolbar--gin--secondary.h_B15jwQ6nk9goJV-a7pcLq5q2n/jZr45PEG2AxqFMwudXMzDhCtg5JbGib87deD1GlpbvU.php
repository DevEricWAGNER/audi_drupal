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

/* themes/contrib/gin/templates/navigation/toolbar--gin--secondary.html.twig */
class __TwigTemplate_f4568975dfddfdebeea9ee6cbf5c3509 extends Template
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
        // line 23
        echo "
";
        // line 25
        if ((($__internal_compile_0 = (($__internal_compile_1 = $context) && is_array($__internal_compile_1) || $__internal_compile_1 instanceof ArrayAccess ? ($__internal_compile_1["#secondary"] ?? null) : null)) && is_array($__internal_compile_0) || $__internal_compile_0 instanceof ArrayAccess ? ($__internal_compile_0["tabs"] ?? null) : null)) {
            // line 26
            echo "  ";
            $context["tabs"] = (($__internal_compile_2 = (($__internal_compile_3 = $context) && is_array($__internal_compile_3) || $__internal_compile_3 instanceof ArrayAccess ? ($__internal_compile_3["#secondary"] ?? null) : null)) && is_array($__internal_compile_2) || $__internal_compile_2 instanceof ArrayAccess ? ($__internal_compile_2["tabs"] ?? null) : null);
        }
        // line 28
        if ((($__internal_compile_4 = (($__internal_compile_5 = $context) && is_array($__internal_compile_5) || $__internal_compile_5 instanceof ArrayAccess ? ($__internal_compile_5["#secondary"] ?? null) : null)) && is_array($__internal_compile_4) || $__internal_compile_4 instanceof ArrayAccess ? ($__internal_compile_4["trays"] ?? null) : null)) {
            // line 29
            echo "  ";
            $context["trays"] = (($__internal_compile_6 = (($__internal_compile_7 = $context) && is_array($__internal_compile_7) || $__internal_compile_7 instanceof ArrayAccess ? ($__internal_compile_7["#secondary"] ?? null) : null)) && is_array($__internal_compile_6) || $__internal_compile_6 instanceof ArrayAccess ? ($__internal_compile_6["trays"] ?? null) : null);
        }
        // line 31
        echo "
<div";
        // line 32
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["attributes"] ?? null), "addClass", ["toolbar", "toolbar-secondary"], "method", false, false, true, 32), "setAttribute", ["id", "toolbar-administration-secondary"], "method", false, false, true, 32), 32, $this->source), "html", null, true);
        echo ">
  <nav";
        // line 33
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["toolbar_attributes"] ?? null), "addClass", ["toolbar-bar", "clearfix"], "method", false, false, true, 33), "setAttribute", ["id", "toolbar-bar"], "method", false, false, true, 33), 33, $this->source), "html", null, true);
        echo ">
    <h2 class=\"visually-hidden\">";
        // line 34
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["toolbar_heading"] ?? null), 34, $this->source), "html", null, true);
        echo "</h2>

    ";
        // line 36
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["tabs"] ?? null));
        foreach ($context['_seq'] as $context["key"] => $context["tab"]) {
            // line 37
            echo "      ";
            $context["tray"] = (($__internal_compile_8 = ($context["trays"] ?? null)) && is_array($__internal_compile_8) || $__internal_compile_8 instanceof ArrayAccess ? ($__internal_compile_8[$context["key"]] ?? null) : null);
            // line 38
            echo "      ";
            $context["user_menu"] = (((($__internal_compile_9 = twig_get_attribute($this->env, $this->source, ($context["tray"] ?? null), "links", [], "any", false, false, true, 38)) && is_array($__internal_compile_9) || $__internal_compile_9 instanceof ArrayAccess ? ($__internal_compile_9["user_links"] ?? null) : null)) ? ("user-menu") : (false));
            // line 39
            echo "      ";
            $context["item_id"] = [];
            // line 40
            echo "
      ";
            // line 41
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable((($__internal_compile_10 = (($__internal_compile_11 = twig_get_attribute($this->env, $this->source, $context["tab"], "link", [], "any", false, false, true, 41)) && is_array($__internal_compile_11) || $__internal_compile_11 instanceof ArrayAccess ? ($__internal_compile_11["#attributes"] ?? null) : null)) && is_array($__internal_compile_10) || $__internal_compile_10 instanceof ArrayAccess ? ($__internal_compile_10["class"] ?? null) : null));
            foreach ($context['_seq'] as $context["key"] => $context["item"]) {
                // line 42
                echo "        ";
                if (twig_in_filter("icon-", $context["item"])) {
                    // line 43
                    echo "          ";
                    $context["item_id"] = twig_array_merge($this->sandbox->ensureToStringAllowed(($context["item_id"] ?? null), 43, $this->source), [("toolbar-id--" . $this->sandbox->ensureToStringAllowed($context["item"], 43, $this->source))]);
                    // line 44
                    echo "        ";
                }
                // line 45
                echo "      ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['key'], $context['item'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 46
            echo "
      ";
            // line 47
            $context["tab_id"] = (((($__internal_compile_12 = twig_get_attribute($this->env, $this->source, $context["tab"], "link", [], "any", false, false, true, 47)) && is_array($__internal_compile_12) || $__internal_compile_12 instanceof ArrayAccess ? ($__internal_compile_12["#id"] ?? null) : null)) ? (("toolbar-tab--" . $this->sandbox->ensureToStringAllowed((($__internal_compile_13 = twig_get_attribute($this->env, $this->source, $context["tab"], "link", [], "any", false, false, true, 47)) && is_array($__internal_compile_13) || $__internal_compile_13 instanceof ArrayAccess ? ($__internal_compile_13["#id"] ?? null) : null), 47, $this->source))) : (""));
            // line 48
            echo "      ";
            $context["tab_classes"] = twig_array_merge($this->sandbox->ensureToStringAllowed(($context["item_id"] ?? null), 48, $this->source), ["toolbar-tab", ($context["user_menu"] ?? null), ($context["tab_id"] ?? null)]);
            // line 49
            echo "
      ";
            // line 50
            $context["denylist_items"] = ["toolbar-id--toolbar-icon-menu"];
            // line 53
            echo "
      ";
            // line 55
            echo "      ";
            if (!twig_in_filter((($__internal_compile_14 = ($context["item_id"] ?? null)) && is_array($__internal_compile_14) || $__internal_compile_14 instanceof ArrayAccess ? ($__internal_compile_14[0] ?? null) : null), ($context["denylist_items"] ?? null))) {
                // line 56
                echo "        <div";
                echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, $context["tab"], "attributes", [], "any", false, false, true, 56), "addClass", [($context["tab_classes"] ?? null)], "method", false, false, true, 56), 56, $this->source), "html", null, true);
                if (twig_in_filter("tour-toolbar-tab", twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, $context["tab"], "attributes", [], "any", false, false, true, 56), "class", [], "any", false, false, true, 56))) {
                    echo " id=\"toolbar-tab-tour\"";
                }
                echo ">
          ";
                // line 57
                if (((($__internal_compile_15 = ($context["item_id"] ?? null)) && is_array($__internal_compile_15) || $__internal_compile_15 instanceof ArrayAccess ? ($__internal_compile_15[0] ?? null) : null) == "toolbar-id--toolbar-icon-user")) {
                    // line 58
                    echo "            ";
                    echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["user_picture"] ?? null), 58, $this->source), "html", null, true);
                    echo "
          ";
                } else {
                    // line 60
                    echo "            ";
                    echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, $context["tab"], "link", [], "any", false, false, true, 60), 60, $this->source), "html", null, true);
                    echo "
          ";
                }
                // line 62
                echo "          <div";
                echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["tray"] ?? null), "attributes", [], "any", false, false, true, 62), 62, $this->source), "html", null, true);
                echo ">
            ";
                // line 63
                if (twig_get_attribute($this->env, $this->source, ($context["tray"] ?? null), "label", [], "any", false, false, true, 63)) {
                    // line 64
                    echo "              <nav class=\"toolbar-lining clearfix\" role=\"navigation\" aria-label=\"";
                    echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["tray"] ?? null), "label", [], "any", false, false, true, 64), 64, $this->source), "html", null, true);
                    echo "\">
                <h3 class=\"toolbar-tray-name visually-hidden\">";
                    // line 65
                    echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["tray"] ?? null), "label", [], "any", false, false, true, 65), 65, $this->source), "html", null, true);
                    echo "</h3>
            ";
                } else {
                    // line 67
                    echo "              <nav class=\"toolbar-lining clearfix\" role=\"navigation\">
            ";
                }
                // line 69
                echo "            ";
                echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["tray"] ?? null), "links", [], "any", false, false, true, 69), 69, $this->source), "html", null, true);
                echo "
            </nav>
          </div>
        </div>
      ";
            }
            // line 74
            echo "    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['key'], $context['tab'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 75
        echo "  </nav>
  ";
        // line 76
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["remainder"] ?? null), 76, $this->source), "html", null, true);
        echo "
</div>
";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["_context", "attributes", "toolbar_attributes", "toolbar_heading", "user_picture", "remainder"]);    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName()
    {
        return "themes/contrib/gin/templates/navigation/toolbar--gin--secondary.html.twig";
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
        return array (  185 => 76,  182 => 75,  176 => 74,  167 => 69,  163 => 67,  158 => 65,  153 => 64,  151 => 63,  146 => 62,  140 => 60,  134 => 58,  132 => 57,  124 => 56,  121 => 55,  118 => 53,  116 => 50,  113 => 49,  110 => 48,  108 => 47,  105 => 46,  99 => 45,  96 => 44,  93 => 43,  90 => 42,  86 => 41,  83 => 40,  80 => 39,  77 => 38,  74 => 37,  70 => 36,  65 => 34,  61 => 33,  57 => 32,  54 => 31,  50 => 29,  48 => 28,  44 => 26,  42 => 25,  39 => 23,);
    }

    public function getSourceContext()
    {
        return new Source("", "themes/contrib/gin/templates/navigation/toolbar--gin--secondary.html.twig", "/var/www/html/web/themes/contrib/gin/templates/navigation/toolbar--gin--secondary.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = array("if" => 25, "set" => 26, "for" => 36);
        static $filters = array("escape" => 32, "merge" => 43);
        static $functions = array();

        try {
            $this->sandbox->checkSecurity(
                ['if', 'set', 'for'],
                ['escape', 'merge'],
                []
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
