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

/* core/modules/system/templates/form-element.html.twig */
class __TwigTemplate_32c54d96b6d676ceb2b78526a540d16e extends Template
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
        // line 50
        $context["classes"] = ["js-form-item", "form-item", ("js-form-type-" . \Drupal\Component\Utility\Html::getClass($this->sandbox->ensureToStringAllowed(        // line 53
($context["type"] ?? null), 53, $this->source))), ("form-item-" . \Drupal\Component\Utility\Html::getClass($this->sandbox->ensureToStringAllowed(        // line 54
($context["name"] ?? null), 54, $this->source))), ("js-form-item-" . \Drupal\Component\Utility\Html::getClass($this->sandbox->ensureToStringAllowed(        // line 55
($context["name"] ?? null), 55, $this->source))), ((!twig_in_filter(        // line 56
($context["title_display"] ?? null), ["after", "before"])) ? ("form-no-label") : ("")), (((        // line 57
($context["disabled"] ?? null) == "disabled")) ? ("form-disabled") : ("")), ((        // line 58
($context["errors"] ?? null)) ? ("form-item--error") : (""))];
        // line 62
        $context["description_classes"] = ["description", (((        // line 64
($context["description_display"] ?? null) == "invisible")) ? ("visually-hidden") : (""))];
        // line 67
        echo "<div";
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["attributes"] ?? null), "addClass", [($context["classes"] ?? null)], "method", false, false, true, 67), 67, $this->source), "html", null, true);
        echo ">
  ";
        // line 68
        if (twig_in_filter(($context["label_display"] ?? null), ["before", "invisible"])) {
            // line 69
            echo "    ";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["label"] ?? null), 69, $this->source), "html", null, true);
            echo "
  ";
        }
        // line 71
        echo "  ";
        if ( !twig_test_empty(($context["prefix"] ?? null))) {
            // line 72
            echo "    <span class=\"field-prefix\">";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["prefix"] ?? null), 72, $this->source), "html", null, true);
            echo "</span>
  ";
        }
        // line 74
        echo "  ";
        if (((($context["description_display"] ?? null) == "before") && twig_get_attribute($this->env, $this->source, ($context["description"] ?? null), "content", [], "any", false, false, true, 74))) {
            // line 75
            echo "    <div";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["description"] ?? null), "attributes", [], "any", false, false, true, 75), 75, $this->source), "html", null, true);
            echo ">
      ";
            // line 76
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["description"] ?? null), "content", [], "any", false, false, true, 76), 76, $this->source), "html", null, true);
            echo "
    </div>
  ";
        }
        // line 79
        echo "  ";
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["children"] ?? null), 79, $this->source), "html", null, true);
        echo "
  ";
        // line 80
        if ( !twig_test_empty(($context["suffix"] ?? null))) {
            // line 81
            echo "    <span class=\"field-suffix\">";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["suffix"] ?? null), 81, $this->source), "html", null, true);
            echo "</span>
  ";
        }
        // line 83
        echo "  ";
        if ((($context["label_display"] ?? null) == "after")) {
            // line 84
            echo "    ";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["label"] ?? null), 84, $this->source), "html", null, true);
            echo "
  ";
        }
        // line 86
        echo "  ";
        if (($context["errors"] ?? null)) {
            // line 87
            echo "    <div class=\"form-item--error-message\">
      ";
            // line 88
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["errors"] ?? null), 88, $this->source), "html", null, true);
            echo "
    </div>
  ";
        }
        // line 91
        echo "  ";
        if ((twig_in_filter(($context["description_display"] ?? null), ["after", "invisible"]) && twig_get_attribute($this->env, $this->source, ($context["description"] ?? null), "content", [], "any", false, false, true, 91))) {
            // line 92
            echo "    <div";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["description"] ?? null), "attributes", [], "any", false, false, true, 92), "addClass", [($context["description_classes"] ?? null)], "method", false, false, true, 92), 92, $this->source), "html", null, true);
            echo ">
      ";
            // line 93
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["description"] ?? null), "content", [], "any", false, false, true, 93), 93, $this->source), "html", null, true);
            echo "
    </div>
  ";
        }
        // line 96
        echo "</div>
";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["type", "name", "title_display", "disabled", "errors", "description_display", "attributes", "label_display", "label", "prefix", "description", "children", "suffix"]);    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName()
    {
        return "core/modules/system/templates/form-element.html.twig";
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
        return array (  134 => 96,  128 => 93,  123 => 92,  120 => 91,  114 => 88,  111 => 87,  108 => 86,  102 => 84,  99 => 83,  93 => 81,  91 => 80,  86 => 79,  80 => 76,  75 => 75,  72 => 74,  66 => 72,  63 => 71,  57 => 69,  55 => 68,  50 => 67,  48 => 64,  47 => 62,  45 => 58,  44 => 57,  43 => 56,  42 => 55,  41 => 54,  40 => 53,  39 => 50,);
    }

    public function getSourceContext()
    {
        return new Source("{#
/**
 * @file
 * Default theme implementation for a form element.
 *
 * Available variables:
 * - attributes: HTML attributes for the containing element.
 * - errors: (optional) Any errors for this form element, may not be set.
 * - prefix: (optional) The form element prefix, may not be set.
 * - suffix: (optional) The form element suffix, may not be set.
 * - required: The required marker, or empty if the associated form element is
 *   not required.
 * - type: The type of the element.
 * - name: The name of the element.
 * - label: A rendered label element.
 * - label_display: Label display setting. It can have these values:
 *   - before: The label is output before the element. This is the default.
 *     The label includes the #title and the required marker, if #required.
 *   - after: The label is output after the element. For example, this is used
 *     for radio and checkbox #type elements. If the #title is empty but the
 *     field is #required, the label will contain only the required marker.
 *   - invisible: Labels are critical for screen readers to enable them to
 *     properly navigate through forms but can be visually distracting. This
 *     property hides the label for everyone except screen readers.
 *   - attribute: Set the title attribute on the element to create a tooltip but
 *     output no label element. This is supported only for checkboxes and radios
 *     in \\Drupal\\Core\\Render\\Element\\CompositeFormElementTrait::preRenderCompositeFormElement().
 *     It is used where a visual label is not needed, such as a table of
 *     checkboxes where the row and column provide the context. The tooltip will
 *     include the title and required marker.
 * - description: (optional) A list of description properties containing:
 *    - content: A description of the form element, may not be set.
 *    - attributes: (optional) A list of HTML attributes to apply to the
 *      description content wrapper. Will only be set when description is set.
 * - description_display: Description display setting. It can have these values:
 *   - before: The description is output before the element.
 *   - after: The description is output after the element. This is the default
 *     value.
 *   - invisible: The description is output after the element, hidden visually
 *     but available to screen readers.
 * - disabled: True if the element is disabled.
 * - title_display: Title display setting.
 *
 * @see template_preprocess_form_element()
 *
 * @ingroup themeable
 */
#}
{%
  set classes = [
    'js-form-item',
    'form-item',
    'js-form-type-' ~ type|clean_class,
    'form-item-' ~ name|clean_class,
    'js-form-item-' ~ name|clean_class,
    title_display not in ['after', 'before'] ? 'form-no-label',
    disabled == 'disabled' ? 'form-disabled',
    errors ? 'form-item--error',
  ]
%}
{%
  set description_classes = [
    'description',
    description_display == 'invisible' ? 'visually-hidden',
  ]
%}
<div{{ attributes.addClass(classes) }}>
  {% if label_display in ['before', 'invisible'] %}
    {{ label }}
  {% endif %}
  {% if prefix is not empty %}
    <span class=\"field-prefix\">{{ prefix }}</span>
  {% endif %}
  {% if description_display == 'before' and description.content %}
    <div{{ description.attributes }}>
      {{ description.content }}
    </div>
  {% endif %}
  {{ children }}
  {% if suffix is not empty %}
    <span class=\"field-suffix\">{{ suffix }}</span>
  {% endif %}
  {% if label_display == 'after' %}
    {{ label }}
  {% endif %}
  {% if errors %}
    <div class=\"form-item--error-message\">
      {{ errors }}
    </div>
  {% endif %}
  {% if description_display in ['after', 'invisible'] and description.content %}
    <div{{ description.attributes.addClass(description_classes) }}>
      {{ description.content }}
    </div>
  {% endif %}
</div>
", "core/modules/system/templates/form-element.html.twig", "/var/www/html/web/core/modules/system/templates/form-element.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = array("set" => 50, "if" => 68);
        static $filters = array("clean_class" => 53, "escape" => 67);
        static $functions = array();

        try {
            $this->sandbox->checkSecurity(
                ['set', 'if'],
                ['clean_class', 'escape'],
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
