/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {

	// Define changes to default configuration here.
	// For complete reference see:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config

    config.customConfig = '/aropixeladmin/ckeditor/config.js';

	// Extra config
	// ------------------------------

	// Remove some buttons provided by the standard plugins, which are
	// not needed in the Standard(s) toolbar.
	config.removeButtons = 'Subscript,Superscript';

	// Set the most common block elements.
	// config.format_tags = 'h2;h3;h4;h5';

	// Simplify the dialog windows.
	config.removeDialogTabs = 'image:advanced;link:advanced';

	// Allow content rules
	config.allowedContent = true;


	// Extra plugins
	// ------------------------------

	// CKEDITOR PLUGINS LOADING
    config.extraPlugins = 'pbckcode,dialogadvtab,videodetector,justify,div,showblocks'; // add other plugins here (comma separated)

	// PBCKCODE CUSTOMIZATION
    config.pbckcode = {
        // An optional class to your pre tag.
        cls : '',

        // The syntax highlighter you will use in the output view
        highlighter : 'PRETTIFY',

        // An array of the available modes for you plugin.
        // The key corresponds to the string shown in the select tag.
        // The value correspond to the loaded file for ACE Editor.
        modes : [ ['HTML', 'html'], ['CSS', 'css'], ['PHP', 'php'], ['JS', 'javascript'] ],

        // The theme of the ACE Editor of the plugin.
        theme : 'textmate',

        // Tab indentation (in spaces)
        tab_size : '4',

        // the root path of ACE Editor. Useful if you want to use the plugin
        // without any Internet connection
        js : "http://cdn.jsdelivr.net//ace/1.1.4/noconflict/"
    };

};

CKEDITOR.on( 'dialogDefinition', function( ev )
   {
        // Take the dialog name and its definition from the event data.
        var dialogName = ev.data.name;
        var dialogDefinition = ev.data.definition;
        var dialogObject = ev.data.definition.dialog;
        var srcTextarea = ev.editor.element.$;

        if (dialogName == "image") {

            dialogDefinition.addContents( {
                id: 'tabCustomImage',
                label: 'Uploader une image',
                title: 'Uploader une image',
                expand: true,
                padding: 0,
                elements: [
                    {
                        type: 'html',
                        html: '<p><strong>Sélectionner une image dans votre bibliothèque</strong></p>'
                    },
                    {
                        type: 'button',
                        id: 'selectImageEditor',
                        label: 'Ouvrir la bibliothèque',
                        onClick: function() {

                            dialogObject.hide();

                            // 		$(ev.editor.element).ImageManager({
                            // 			editor : ev.editor,
                            //    multiple : true,
                            // 	model_id : $(srcTextarea).data('id'),
                            // 	category : $(srcTextarea).data('category'),
                            //            });

                            $(srcTextarea).ImageManager({
                                editor : ev.editor,
                                category : $(srcTextarea).attr('data-class'),
                                attach_path : $(srcTextarea).attr('data-attach-path'),
                            });

                            return (false);

                        }
                    }
                ]
            } );
        }


       if (dialogName == "link") {

           dialogDefinition.addContents( {
               id: 'tabCustomFile',
               label: 'Uploader un fichier',
               title: 'Uploader un fichier',
               expand: true,
               padding: 0,
               elements: [
                   {
                       type: 'html',
                       html: '<p><strong>Sélectionner un fichier dans votre bibliothèque</strong></p>'
                   },
                   {
                       type: 'button',
                       id: 'selectFileEditor',
                       label: 'Ouvrir la bibliothèque',
                       onClick: function() {

                           // dialogObject.hide();

                           // 		$(ev.editor.element).ImageManager({
                           // 			editor : ev.editor,
                           //    multiple : true,
                           // 	model_id : $(srcTextarea).data('id'),
                           // 	category : $(srcTextarea).data('category'),
                           //            });
                            $(srcTextarea).FileManager({
                                editor : ev.editor,
                                category : $(srcTextarea).data('category-file'),
                            });

                            return (false);

                       }
                   }
               ]
           } );
       }

   });
