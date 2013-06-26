/* Tag it! ~ Tag editor and suggestions
 * Modified version of the script by Levy Carneiro Jr:
 * http://levycarneiro.com/projects/tag-it/example.html
 */
(function($) {

	$.fn.tagit = function(options) {
		
		// prepend a ul list and hide the existing input field
		$(this).before("<ul></ul>");
		var fl = $(this);
		var el = $(this).prev("ul");
		
		var BACKSPACE		= 8;
		var ENTER			= 13;
		var SPACE			= 32;
		var COMMA			= 44;

		// add the tagit CSS class.
		el.addClass("tagit");
		fl.hide();

		// create the input field.
		var html_input_field = "<li class=\"tagit-new\"><input class=\"tagit-input\" type=\"text\" /></li>\n";
		el.html (html_input_field);

		tag_input = el.children(".tagit-new").children(".tagit-input");
		// load the previously generated tags
		load_tags();
		
		el.click(function(e){
			if (e.target.tagName == 'A') {
				// Removes a tag when the little 'x' is clicked.
				// Event is binded to the UL, otherwise a new tag (LI > A) wouldn't have this event attached to it.
				$(e.target).parent().remove();
				
				update_tags();
			}
			else {
				// Sets the focus() to the input field, if the user clicks anywhere inside the UL.
				// This is needed because the input field needs to be of a small size.
				tag_input.focus();
			}
		});

		tag_input.keypress(function(event){
			if (event.which == BACKSPACE) {
				if (tag_input.val() == "") {
					// When backspace is pressed, the last tag is deleted.
					$(el).children(".tagit-choice:last").remove();
					
					update_tags();
				}
			}
			// Comma/Space/Enter are all valid delimiters for new tags.
			else if (event.which == COMMA || event.which == SPACE || event.which == ENTER) {
				event.preventDefault();

				var typed = tag_input.val();
				typed = typed.replace(/,+$/,"");
				typed = typed.trim();

				if (typed != "") {
					if (is_new (typed)) {
						create_choice (typed);
					}
					// Cleaning the input.
					tag_input.val("");
				}
			}
		});

		tag_input.autocomplete({
			source: options.availableTags, 
			select: function(event,ui){
				if (is_new (ui.item.value)) {
					create_choice (ui.item.value);
				}
				// Cleaning the input.
				tag_input.val("");

				// Preventing the tag input to be update with the chosen value.
				return false;
			}
		});

		function is_new (value){
			var is_new = true;
			this.tag_input.parents("ul").children(".tagit-choice").each(function(i){
				n = $(this).children("input").val();
				if (value == n) {
					is_new = false;
				}
			})
			return is_new;
		}
		function create_choice (value){
			var el = "";
			el  = "<li class=\"tagit-choice\">\n";
			el += value + "\n";
			el += "<a class=\"close\">x</a>\n";
			el += "<input type=\"hidden\" style=\"display:none;\" value=\""+value+"\" name=\"item[tags]\">\n";
			el += "</li>\n";
			var li_search_tags = this.tag_input.parent();
			$(el).insertBefore (li_search_tags);
			this.tag_input.val("");
			
			update_tags();
			
		}
		
		function load_tags (){
			var tags = fl.val().split(",");
			if(tags == ""){ 
				// do nothing
			} else {
				$(tags).each(function(){
					create_choice (this);
				});
			}
			
		}
		
		function update_tags (){
			var tags = new Array();
			el.find("input[name='item[tags]']").each(function(){
				tags.push( $(this).val() );
			});
			fl.attr("value", tags.join(","));
		}
	};

	String.prototype.trim = function() {
		return this.replace(/^\s+|\s+$/g,"");
	};

})(jQuery);
