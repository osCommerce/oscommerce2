function SetFocus() {
  if (document.forms.length > 0) {
    for (f=0; f<document.forms.length; f++) {
      if (document.forms[f].name != "adminlanguage") {
        var field = document.forms[f];
        for (i=0; i<field.length; i++) {
          if ( (field.elements[i].type != "image") &&
               (field.elements[i].type != "hidden") &&
               (field.elements[i].type != "reset") &&
               (field.elements[i].type != "submit") ) {

            document.forms[f].elements[i].focus();

            if ( (field.elements[i].type == "text") ||
                 (field.elements[i].type == "password") )
              document.forms[f].elements[i].select();

            break;
          }
        }
      }
    }
  }
}

function rowOverEffect(object) {
  if (object.className == 'dataTableRow') object.className = 'dataTableRowOver';
}

function rowOutEffect(object) {
  if (object.className == 'dataTableRowOver') object.className = 'dataTableRow';
}
