/*! DataTables Bootstrap 3 integration
 * © SpryMedia Ltd - datatables.net/license
 */

import jQuery from 'jquery';
import DataTable from 'datatables.net';

// Allow reassignment of the $ variable
let $ = jQuery;


/**
 * DataTables integration for Bootstrap 3.
 *
 * This file sets the defaults and adds options to DataTables to style its
 * controls using Bootstrap. See https://datatables.net/manual/styling/bootstrap
 * for further information.
 */

/* Set the defaults for DataTables initialisation */
$.extend( true, DataTable.defaults, {
	renderer: 'bootstrap'
} );


/* Default class modification */
$.extend( true, DataTable.ext.classes, {
	container: "dt-container form-inline dt-bootstrap",
	search: {
		input: "form-control input-sm"
	},
	length: {
		select: "form-control input-sm"
	},
	processing: {
		container: "dt-processing panel panel-default"
	}
} );

/* Bootstrap paging button renderer */
DataTable.ext.renderer.pagingButton.bootstrap = function (settings, buttonType, content, active, disabled) {
	var btnClasses = ['dt-paging-button', 'page-item'];

	if (active) {
		btnClasses.push('active');
	}

	if (disabled) {
		btnClasses.push('disabled')
	}

	var li = $('<li>').addClass(btnClasses.join(' '));
	var a = $('<a>', {
		'href': disabled ? null : '#',
		'class': 'page-link'
	})
		.html(content)
		.appendTo(li);

	return {
		display: li,
		clicker: a
	};
};

DataTable.ext.renderer.pagingContainer.bootstrap = function (settings, buttonEls) {
	return $('<ul/>').addClass('pagination').append(buttonEls);
};

DataTable.ext.renderer.layout.bootstrap = function ( settings, container, items ) {
	var row = $( '<div/>', {
			"class": 'row'
		} )
		.appendTo( container );

	$.each( items, function (key, val) {
		var klass = '';
		if ( key === 'start' ) {
			klass += 'col-sm-6 text-left';
		}
		else if ( key === 'end' ) {
			klass += 'col-sm-6 text-right';

			// If no left element, we need to offset this one
			if (row.find('.col-sm-6').length === 0) {
				klass += ' col-sm-offset-6';
			}
		}
		else if ( key === 'full' ) {
			klass += 'col-sm-12';
			if ( ! val.table ) {
				klass += ' text-center';
			}
		}

		$( '<div/>', {
				id: val.id || null,
				"class": klass+' '+(val.className || '')
			} )
			.append( val.contents )
			.appendTo( row );
	} );
};


export default DataTable;
