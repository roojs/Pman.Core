//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Roo.namespace('Pman.Dialog');

Pman.Dialog.CoreViewWebsite = {

 _strings : {
  '1e35fe802ad1aaf4414fd68ad3157675' :"View Website",
  'a60852f204ed8028c1c58808b746d115' :"Ok"
 },

 dialog : false,
 callback:  false,
 
 /**
  * Pretty print XML content with proper indentation
  */
 prettyPrintXML: function(xmlString) {
     try {
         // Parse XML
         var parser = new DOMParser();
         var xmlDoc = parser.parseFromString(xmlString.trim(), "application/xml");
         
         // Check for parsing errors
         var parseError = xmlDoc.getElementsByTagName("parsererror");
         if (parseError.length > 0) {
             return xmlString; // Return original if parsing fails
         }
         
         // Format XML with indentation
         return this.formatXML(xmlDoc.documentElement, 0);
     } catch (e) {
         return xmlString; // Return original if any error occurs
     }
 },
 
 /**
  * Recursively format XML elements with indentation
  */
 formatXML: function(node, indentLevel) {
     var indent = "  ".repeat(indentLevel);
     var result = "";
     
     if (node.nodeType === Node.ELEMENT_NODE) {
         // Opening tag
         result += indent + "<" + node.tagName;
         
         // Add attributes
         for (var i = 0; i < node.attributes.length; i++) {
             var attr = node.attributes[i];
             result += " " + attr.name + '="' + attr.value + '"';
         }
         
         // Check if element has children
         var hasChildren = false;
         var textContent = "";
         var childElements = [];
         
         for (var i = 0; i < node.childNodes.length; i++) {
             var child = node.childNodes[i];
             if (child.nodeType === Node.ELEMENT_NODE) {
                 hasChildren = true;
                 childElements.push(child);
             } else if (child.nodeType === Node.TEXT_NODE && child.textContent.trim()) {
                 textContent = child.textContent.trim();
             }
         }
         
         if (hasChildren) {
             // Element has child elements
             result += ">\n";
             for (var i = 0; i < childElements.length; i++) {
                 result += this.formatXML(childElements[i], indentLevel + 1);
             }
             result += indent + "</" + node.tagName + ">\n";
         } else if (textContent) {
             // Element has text content
             result += ">" + textContent + "</" + node.tagName + ">\n";
         } else {
             // Self-closing element
             result += "/>\n";
         }
     }
     
     return result;
 },

 show : function(data, cb)
 {
  if (!this.dialog) {
   this.create();
  }

  this.callback = cb;
  this.data = data;
  this.dialog.show.apply(this.dialog,  Array.prototype.slice.call(arguments).slice(2));
  if (this.form) {
   this.form.reset();
   this.form.setValues(data);
   this.form.fireEvent('actioncomplete', this.form,  { type: 'setdata', data: data });
  }

 },

 create : function()
 {
   var _this = this;
   this.dialog = Roo.factory({
    xtype : 'LayoutDialog',
    closable : true,
    collapsible : false,
    draggable : false,
    height : 400,
    modal : true,
    resizable : false,
    title : _this._strings['1e35fe802ad1aaf4414fd68ad3157675'] /* View Website */,
    width : 600,
    listeners : {
     show : function (_self)
      {
          // reset
          _this.websiteViewPanel.setContent('');
          _this.websiteViewPanel.el.dom.parentElement.style.overflow = 'hidden';
          
          var url = false;
          
          if(typeof(_this.data.url) !== 'undefined') {
              url = _this.data.url;
          }
          
          if(url === false) {
              Roo.MessageBox.alert("Error", "Missing url");
              return;
          }
          
          _this.websiteViewPanel.setContent('Loading...');
          
          _this.dialog.setTitle('View URL: ' + url);
          
          var vw = Roo.lib.Dom.getViewWidth();
          var vh = Roo.lib.Dom.getViewHeight();
          _this.dialog.resizeTo(vw * 0.9, vh * 0.9);
          _this.dialog.moveTo(vw * 0.05, vh * 0.05);
          
          var size = _this.dialog.layout.getRegion('center').el.getSize();
          
          
          // different origin
          if (!url.startsWith('/')) {
              // allow scroll
              _this.websiteViewPanel.el.dom.parentElement.style.overflow = 'auto';
              fetch(baseURL + '/Core/ViewWebsite', {
                  method: 'POST',
                  headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                  body: new URLSearchParams({"url": url})
              })
              .then(function(res) {
                  var contentType = res.headers.get('Content-Type');
                  if (contentType && contentType.includes('application/json')) {
                      return res.json().then(function(json) {
                          var formatted = '<pre>' + JSON.stringify(json, null, 2) + '</pre>';
                          _this.websiteViewPanel.setContent(formatted);
                      });
                  } else if (contentType && contentType.includes('application/rss+xml')) { 
                      return res.text().then(function(xml) {
                          Roo.log("XML");
                          Roo.log(xml);
                          
                          // Pretty print XML using custom function
                          var prettyXML = _this.prettyPrintXML(xml);
                          
                          var escaped = prettyXML
                            .replace(/&/g, '&amp;')
                            .replace(/</g, '&lt;')
                            .replace(/>/g, '&gt;');
                          _this.websiteViewPanel.setContent('<pre style="white-space: pre-wrap;">' + escaped + '</pre>');
                      });
                  } else {
                      return res.text().then(function(html) {
                          _this.websiteViewPanel.setContent('<div>' + html + '</div>');
                      });
                  }
              });
              return;
          }
          
          _this.websiteViewPanel.setContent(
              '<iframe ' + 
              'style="border: 0px;width:' + size.width +'px;height:' + size.height + 'px" ' +
              'src="' + url + '"/>'
          );
      }
    },
    xns : Roo,
    '|xns' : 'Roo',
    center : {
     xtype : 'LayoutRegion',
     xns : Roo,
     '|xns' : 'Roo'
    },
    buttons : [
     {
      xtype : 'Button',
      text : _this._strings['a60852f204ed8028c1c58808b746d115'] /* Ok */,
      listeners : {
       click : function (_self, e)
        {
            _this.dialog.hide();
        }
      },
      xns : Roo,
      '|xns' : 'Roo'
     }
    ],
    items  : [
     {
      xtype : 'ContentPanel',
      region : 'center',
      listeners : {
       render : function (_self)
        {
            _this.websiteViewPanel = this;
        }
      },
      xns : Roo,
      '|xns' : 'Roo'
     }
    ]
   });
 }
};
