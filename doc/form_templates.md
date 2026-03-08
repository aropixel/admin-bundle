# Form Templates Documentation

This documentation explains how to create Twig templates for the administration forms (creation or edition) using the base layouts provided by `AropixelAdminBundle`.

## Base Layout

The main template to extend is `@AropixelAdmin/Form/base.html.twig`. This layout provides several blocks to structure your page.

### Basic Structure

Here is a complete example of a template for adding or editing an entity (e.g., an `Artist`):

```twig
{% extends '@AropixelAdmin/Form/base.html.twig' %}
{% import '@AropixelAdmin/Macro/breadcrumb.html.twig' as nav %}
{% import '@AropixelAdmin/Macro/forms.html.twig' as forms %}

{# 1. Meta and Titles #}
{% block meta_title %}{% if artist.id %}Modifier{% else %}Ajouter{% endif %} un artiste{% endblock %}

{% block header_title %}{% if artist.id %}{{ artist.name }}{% else %}Ajouter un artiste{% endif %}{% endblock %}

{# 2. Breadcrumb #}
{% block header_breadcrumb %}
    {{ nav.breadcrumbs([
        { label: 'text.home', url: url('_admin') },
        { label: 'Programmation' },
        { label: (artist.id ? 'Modifier' : 'Ajouter') ~ ' un artiste' }
    ]) }}
{% endblock %}

{# 3. Tabs Navigation (Optional) #}
{% block tabbable %}
    {{ forms.tabs([
        { id: 'panel-tab-artist', label: 'Artiste' },
        { id: 'panel-tab-badge', label: 'Badge' },
        { id: 'panel-tab-prog', label: 'Programmation' }
    ]) }}
{% endblock %}

{# 4. Form Custom Themes #}
{% form_theme form.shows with _self only %}
{% block _artist_shows_entry_widget %}
    {{ form_row(form.day) }}
    {{ form_row(form.stage) }}
    <div class="d-flex flex-column flex-md-row justify-content-between">
        <div class="form-group mb-0">
            <div class="form-label mb-0 me-3 d-flex align-items-center">
                {{ form_label(form.startTime) }}
            </div>
            <div class="form-content">{{ form_widget(form.startTime) }}</div>
        </div>
        <div class="form-group mb-0">
            <div class="form-label mb-0 me-3 d-flex align-items-center">
                {{ form_label(form.endTime) }}
            </div>
            <div class="form-content">{{ form_widget(form.endTime) }}</div>
        </div>
    </div>
{% endblock %}

{# 5. Main Content Panel #}
{% block mainPanel %}

    {# Tab 1: Artist Basic Info #}
    <div class="tab-pane active" id="panel-tab-artist">
        <div class="card card-centered card-centered-large">
            <div class="card-body">
                {{ form_row(form.name) }}
                {{ form_row(form.description) }}
                {{ form_row(form.image) }}
            </div>
        </div>
    </div>

    {# Tab 2: Badges #}
    <div class="tab-pane" id="panel-tab-badge">
        <div class="card card-centered card-centered-large">
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> Vous pouvez choisir <strong>soit une image, soit un label texte</strong> pour le badge.
                </div>
                {{ form_row(form.badgeLabel) }}
                <div class="text-center my-3"><strong>OU</strong></div>
                {{ form_row(form.badge) }}
            </div>
        </div>
    </div>

    {# Tab 3: Collections (Shows) #}
    <div class="tab-pane" id="panel-tab-prog">
        <div class="card card-centered card-centered-large">
            <div class="card-body">
                <div class="form-group mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-2 w-100">
                        <label class="control-label">Séances</label>
                        {# Button to add a new item in the collection #}
                        <a class="btn btn-primary btn-xs" data-form-collection-add="{{ form.shows.vars.id }}">
                            <i class="fa fa-plus"></i> Ajouter une séance
                        </a>
                    </div>
                    {{ form_widget(form.shows, {'attr': {'class': 'w-100'}}) }}
                </div>
            </div>
        </div>
    </div>

{% endblock %}
```

## Blocks Description

- `meta_title`: The title displayed in the browser tab.
- `header_title`: The main title displayed at the top of the page.
- `header_breadcrumb`: The breadcrumb navigation.
- `tabbable`: Define the navigation tabs here if your form is divided into sections.
- `mainPanel`: This is where you put your form rows and content. It usually contains one or more `tab-pane` if you used tabs.

## Form Layout Components

To keep a consistent look, use the following CSS classes:

- `.card.card-centered.card-centered-large`: Wraps the form in a centered container.
- `.card-body`: Standard card padding.

## Collections Handling

To manage Symfony collections in your templates:

1. Use `form_widget(form.myCollection)` to render the collection.
2. Add a button with the `data-form-collection-add="{{ form.myCollection.vars.id }}"` attribute to enable the "Add new item" functionality.
3. You can use `{% form_theme form.myCollection with _self only %}` and define a block like `{% block _collection_id_entry_widget %}` to customize the rendering of each item.
