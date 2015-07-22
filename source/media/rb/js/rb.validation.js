/* jqBootstrapValidation
 * A plugin for automating validation on Twitter Bootstrap formatted forms.
 *
 * v1.3.6
 *
 * License: MIT <http://opensource.org/licenses/mit-license.php> - see LICENSE file
 *
 * http://ReactiveRaven.github.com/jqBootstrapValidation/
 */

(function( $ ){

	var createdElements = [];

	var defaults = {
		options: {
			prependExistingHelpBlock: false,
			sniffHtml: true, // sniff for 'required', 'maxlength', etc
			preventSubmit: true, // stop the form submit event from firing if validation fails
			submitError: false, // function called if there is an error when trying to submit
			submitSuccess: false, // function called just before a successful submit event is sent to the server
            semanticallyStrict: false, // set to true to tidy up generated HTML output
			autoAdd: {
				helpBlocks: true
			},
            filter: function () {
                // return $(this).is(":visible"); // only validate elements you can see
                return true; // validate everything
            }
		},
    methods: {
      init : function( options ) {

        var settings = $.extend(true, {}, defaults);

        settings.options = $.extend(true, settings.options, options);

        var $siblingElements = this;

        var uniqueForms = $.unique(
          $siblingElements.map( function () {
            return $(this).parents("form")[0];
          }).toArray()
        );

        $(uniqueForms).bind("submit", function (e) {
          var $form = $(this);
          var warningsFound = 0;
          var $inputs = $form.find("input,textarea,select").not("[type=submit],[type=image]").filter(settings.options.filter);
          $inputs.trigger("submit.validation").trigger("validationLostFocus.validation");

          $inputs.each(function (i, el) {
            var $this = $(el),
              $controlGroup = $this.parents(".control-group").first();
            if (
              $controlGroup.hasClass("warning")
            ) {
              $controlGroup.removeClass("warning").addClass("error");
              warningsFound++;
            }
          });

          $inputs.trigger("validationLostFocus.validation");

          if (warningsFound) {
            if (settings.options.preventSubmit) {
              e.preventDefault();
            }
            $form.addClass("error");
            if ($.isFunction(settings.options.submitError)) {
              settings.options.submitError($form, e, $inputs.jqBootstrapValidation("collectErrors", true));
            }
          } else {
            $form.removeClass("error");
            if ($.isFunction(settings.options.submitSuccess)) {
              settings.options.submitSuccess($form, e);
            }
          }
        });

        return this.each(function(){

          // Get references to everything we're interested in
          var $this = $(this),
            $controlGroup = $this.parents(".control-group").first(),
            $helpBlock = $controlGroup.find(".help-block").first(),
            $form = $this.parents("form").first(),
            validatorNames = [];

          // create message container if not exists
          if (!$helpBlock.length && settings.options.autoAdd && settings.options.autoAdd.helpBlocks) {
              $helpBlock = $('<div class="help-block" />');
              $controlGroup.find('.controls').append($helpBlock);
							createdElements.push($helpBlock[0]);
          }

          // =============================================================
          //                                     SNIFF HTML FOR VALIDATORS
          // =============================================================

          // *snort sniff snuffle*

          if (settings.options.sniffHtml) {
            var message = "";
            // ---------------------------------------------------------
            //                                                   PATTERN
            // ---------------------------------------------------------
            if ($this.attr("pattern") !== undefined) {
              message = "Not in the expected format<!-- data-validation-pattern-message to override -->";
              if ($this.data("validationPatternMessage")) {
                message = $this.data("validationPatternMessage");
              }
              $this.data("validationPatternMessage", message);
              $this.data("validationPatternRegex", $this.attr("pattern"));
            }
            // ---------------------------------------------------------
            //                                                       MAX
            // ---------------------------------------------------------
            if ($this.attr("max") !== undefined || $this.attr("aria-valuemax") !== undefined) {
              var max = ($this.attr("max") !== undefined ? $this.attr("max") : $this.attr("aria-valuemax"));
              message = "Too high: Maximum of '" + max + "'<!-- data-validation-max-message to override -->";
              if ($this.data("validationMaxMessage")) {
                message = $this.data("validationMaxMessage");
              }
              $this.data("validationMaxMessage", message);
              $this.data("validationMaxMax", max);
            }
            // ---------------------------------------------------------
            //                                                       MIN
            // ---------------------------------------------------------
            if ($this.attr("min") !== undefined || $this.attr("aria-valuemin") !== undefined) {
              var min = ($this.attr("min") !== undefined ? $this.attr("min") : $this.attr("aria-valuemin"));
              message = "Too low: Minimum of '" + min + "'<!-- data-validation-min-message to override -->";
              if ($this.data("validationMinMessage")) {
                message = $this.data("validationMinMessage");
              }
              $this.data("validationMinMessage", message);
              $this.data("validationMinMin", min);
            }
            // ---------------------------------------------------------
            //                                                 MAXLENGTH
            // ---------------------------------------------------------
            if ($this.attr("maxlength") !== undefined) {
              message = "Too long: Maximum of '" + $this.attr("maxlength") + "' characters<!-- data-validation-maxlength-message to override -->";
              if ($this.data("validationMaxlengthMessage")) {
                message = $this.data("validationMaxlengthMessage");
              }
              $this.data("validationMaxlengthMessage", message);
              $this.data("validationMaxlengthMaxlength", $this.attr("maxlength"));
            }
            // ---------------------------------------------------------
            //                                                 MINLENGTH
            // ---------------------------------------------------------
            if ($this.attr("minlength") !== undefined) {
              message = "Too short: Minimum of '" + $this.attr("minlength") + "' characters<!-- data-validation-minlength-message to override -->";
              if ($this.data("validationMinlengthMessage")) {
                message = $this.data("validationMinlengthMessage");
              }
              $this.data("validationMinlengthMessage", message);
              $this.data("validationMinlengthMinlength", $this.attr("minlength"));
            }
            // ---------------------------------------------------------
            //                                                  REQUIRED
            // ---------------------------------------------------------
            if ($this.attr("required") !== undefined || $this.attr("aria-required") !== undefined || $this.hasClass('required')) {
              message = settings.builtInValidators.required.message;
              if ($this.data("validationRequiredMessage")) {
                message = $this.data("validationRequiredMessage");
              }
              $this.data("validationRequiredMessage", message);
            }
            // ---------------------------------------------------------
            //                                                    NUMBER
            // ---------------------------------------------------------
            if (($this.attr("type") !== undefined && $this.attr("type").toLowerCase() === "number")
            		|| $this.hasClass('validate-number')) {
              message = settings.builtInValidators.number.message;
              if ($this.data("validationNumberMessage")) {
                message = $this.data("validationNumberMessage");
              }
              $this.data("validationNumberMessage", message);
            }
            // ---------------------------------------------------------
            //                                                     EMAIL
            // ---------------------------------------------------------
            if (($this.attr("type") !== undefined && $this.attr("type").toLowerCase() === "email")
            		|| $this.hasClass('validate-email')) {
              message = "Not a valid email address<!-- data-validator-validemail-message to override -->";
              if ($this.data("validationValidemailMessage")) {
                message = $this.data("validationValidemailMessage");
              } else if ($this.data("validationEmailMessage")) {
                message = $this.data("validationEmailMessage");
              }
              $this.data("validationValidemailMessage", message);
            }
            // ---------------------------------------------------------
            //                                                MINCHECKED
            // ---------------------------------------------------------
            if ($this.attr("minchecked") !== undefined) {
              message = "Not enough options checked; Minimum of '" + $this.attr("minchecked") + "' required<!-- data-validation-minchecked-message to override -->";
              if ($this.data("validationMincheckedMessage")) {
                message = $this.data("validationMincheckedMessage");
              }
              $this.data("validationMincheckedMessage", message);
              $this.data("validationMincheckedMinchecked", $this.attr("minchecked"));
            }
            // ---------------------------------------------------------
            //                                                MAXCHECKED
            // ---------------------------------------------------------
            if ($this.attr("maxchecked") !== undefined) {
              message = "Too many options checked; Maximum of '" + $this.attr("maxchecked") + "' required<!-- data-validation-maxchecked-message to override -->";
              if ($this.data("validationMaxcheckedMessage")) {
                message = $this.data("validationMaxcheckedMessage");
              }
              $this.data("validationMaxcheckedMessage", message);
              $this.data("validationMaxcheckedMaxchecked", $this.attr("maxchecked"));
            }
          }

          // =============================================================
          //                                       COLLECT VALIDATOR NAMES
          // =============================================================

          // Get named validators
          if ($this.data("validation") !== undefined) {
            validatorNames = $this.data("validation").split(",");
          }

          // Get extra ones defined on the element's data attributes
          $.each($this.data(), function (i, el) {
            var parts = i.replace(/([A-Z])/g, ",$1").split(",");
            if (parts[0] === "validation" && parts[1]) {
              validatorNames.push(parts[1]);
            }
          });

          // =============================================================
          //                                     NORMALISE VALIDATOR NAMES
          // =============================================================

          var validatorNamesToInspect = validatorNames;
          var newValidatorNamesToInspect = [];

          do // repeatedly expand 'shortcut' validators into their real validators
          {
            // Uppercase only the first letter of each name
            $.each(validatorNames, function (i, el) {
              validatorNames[i] = formatValidatorName(el);
            });

            // Remove duplicate validator names
            validatorNames = $.unique(validatorNames);

            // Pull out the new validator names from each shortcut
            newValidatorNamesToInspect = [];
            $.each(validatorNamesToInspect, function(i, el) {
              if ($this.data("validation" + el + "Shortcut") !== undefined) {
                // Are these custom validators?
                // Pull them out!
                $.each($this.data("validation" + el + "Shortcut").split(","), function(i2, el2) {
                  newValidatorNamesToInspect.push(el2);
                });
              } else if (settings.builtInValidators[el.toLowerCase()]) {
                // Is this a recognised built-in?
                // Pull it out!
                var validator = settings.builtInValidators[el.toLowerCase()];
                if (validator.type.toLowerCase() === "shortcut") {
                  $.each(validator.shortcut.split(","), function (i, el) {
                    el = formatValidatorName(el);
                    newValidatorNamesToInspect.push(el);
                    validatorNames.push(el);
                  });
                }
              }
            });

            validatorNamesToInspect = newValidatorNamesToInspect;

          } while (validatorNamesToInspect.length > 0)

          // =============================================================
          //                                       SET UP VALIDATOR ARRAYS
          // =============================================================

          var validators = {};

          $.each(validatorNames, function (i, el) {
            // Set up the 'override' message
            var message = $this.data("validation" + el + "Message");
            var hasOverrideMessage = (message !== undefined);
            var foundValidator = false;
            message =
              (
                message
                  ? message
                  : "'" + el + "' validation failed <!-- Add attribute 'data-validation-" + el.toLowerCase() + "-message' to input to change this message -->"
              )
            ;

            $.each(
              settings.validatorTypes,
              function (validatorType, validatorTemplate) {
                if (validators[validatorType] === undefined) {
                  validators[validatorType] = [];
                }
                if (!foundValidator && $this.data("validation" + el + formatValidatorName(validatorTemplate.name)) !== undefined) {
                  validators[validatorType].push(
                    $.extend(
                      true,
                      {
                        name: formatValidatorName(validatorTemplate.name),
                        message: message
                      },
                      validatorTemplate.init($this, el)
                    )
                  );
                  foundValidator = true;
                }
              }
            );

            if (!foundValidator && settings.builtInValidators[el.toLowerCase()]) {

              var validator = $.extend(true, {}, settings.builtInValidators[el.toLowerCase()]);
              if (hasOverrideMessage) {
                validator.message = message;
              }
              var validatorType = validator.type.toLowerCase();

              if (validatorType === "shortcut") {
                foundValidator = true;
              } else {
                $.each(
                  settings.validatorTypes,
                  function (validatorTemplateType, validatorTemplate) {
                    if (validators[validatorTemplateType] === undefined) {
                      validators[validatorTemplateType] = [];
                    }
                    if (!foundValidator && validatorType === validatorTemplateType.toLowerCase()) {
                      $this.data("validation" + el + formatValidatorName(validatorTemplate.name), validator[validatorTemplate.name.toLowerCase()]);
                      validators[validatorType].push(
                        $.extend(
                          validator,
                          validatorTemplate.init($this, el)
                        )
                      );
                      foundValidator = true;
                    }
                  }
                );
              }
            }

            if (! foundValidator) {
              $.error("Cannot find validation info for '" + el + "'");
            }
          });

          // =============================================================
          //                                         STORE FALLBACK VALUES
          // =============================================================

          $helpBlock.data(
            "original-contents",
            (
              $helpBlock.data("original-contents")
                ? $helpBlock.data("original-contents")
                : $helpBlock.html()
            )
          );

          $helpBlock.data(
            "original-role",
            (
              $helpBlock.data("original-role")
                ? $helpBlock.data("original-role")
                : $helpBlock.attr("role")
            )
          );

          $controlGroup.data(
            "original-classes",
            (
              $controlGroup.data("original-clases")
                ? $controlGroup.data("original-classes")
                : $controlGroup.attr("class")
            )
          );

          $this.data(
            "original-aria-invalid",
            (
              $this.data("original-aria-invalid")
                ? $this.data("original-aria-invalid")
                : $this.attr("aria-invalid")
            )
          );

          // =============================================================
          //                                                    VALIDATION
          // =============================================================

          $this.bind(
            "validation.validation",
            function (event, params) {

              var value = getValue($this);

              // Get a list of the errors to apply
              var errorsFound = [];

              $.each(validators, function (validatorType, validatorTypeArray) {
                if (value || value.length || (params && params.includeEmpty) || (!!settings.validatorTypes[validatorType].blockSubmit && params && !!params.submitting)) {
                  $.each(validatorTypeArray, function (i, validator) {
                    if (settings.validatorTypes[validatorType].validate($this, value, validator)) {
                      errorsFound.push(validator.message);
                    }
                  });
                }
              });

              return errorsFound;
            }
          );

          $this.bind(
            "getValidators.validation",
            function () {
              return validators;
            }
          );

          // =============================================================
          //                                             WATCH FOR CHANGES
          // =============================================================
          $this.bind(
            "submit.validation",
            function () {
              return $this.triggerHandler("change.validation", {submitting: true});
            }
          );
          $this.bind(
            [
              "keyup",
              "focus",
              "blur",
              "click",
              "keydown",
              "keypress",
              "change"
            ].join(".validation ") + ".validation",
            function (e, params) {

              var value = getValue($this);

              var errorsFound = [];

              $controlGroup.find("input,textarea,select").each(function (i, el) {
            	// return in case if field does not have any name
            	if(typeof(el) == 'undefined' || el.name == ""){
            		return false;
            	}
            	
                var oldCount = errorsFound.length;
                $.each($(el).triggerHandler("validation.validation", params), function (j, message) {
                  errorsFound.push(message);
                });
                if (errorsFound.length > oldCount) {
                  $(el).attr("aria-invalid", "true");
                } else {
                  var original = $this.data("original-aria-invalid");
                  $(el).attr("aria-invalid", (original !== undefined ? original : false));
                }
              });

              $form.find("input,select,textarea").not($this).not("[name=\"" + $this.attr("name") + "\"]").trigger("validationLostFocus.validation");

              errorsFound = $.unique(errorsFound.sort());

              // Were there any errors?
              if (errorsFound.length) {
                // Better flag it up as a warning.
                $controlGroup.removeClass("success error").addClass("warning");

                // How many errors did we find?
                if (settings.options.semanticallyStrict && errorsFound.length === 1) {
                  // Only one? Being strict? Just output it.
                  $helpBlock.html(errorsFound[0] + 
                    ( settings.options.prependExistingHelpBlock ? $helpBlock.data("original-contents") : "" ));
                } else {
                  // Multiple? Being sloppy? Glue them together into an UL.
                  $helpBlock.html("<ul role=\"alert\"><li>" + errorsFound.join("</li><li>") + "</li></ul>" +
                    ( settings.options.prependExistingHelpBlock ? $helpBlock.data("original-contents") : "" ));
                }
              } else {
                $controlGroup.removeClass("warning error success");
                if (value!= null && value.length > 0) {
                  $controlGroup.addClass("success");
                }
                $helpBlock.html($helpBlock.data("original-contents"));
              }

              if (e.type === "blur") {
                $controlGroup.removeClass("success");
              }
            }
          );
          $this.bind("validationLostFocus.validation", function () {
            $controlGroup.removeClass("success");
          });
        });
      },
      destroy : function( ) {

        return this.each(
          function() {

            var
              $this = $(this),
              $controlGroup = $this.parents(".control-group").first(),
              $helpBlock = $controlGroup.find(".help-block").first();

            // remove our events
            $this.unbind('.validation'); // events are namespaced.
            // reset help text
            $helpBlock.html($helpBlock.data("original-contents"));
            // reset classes
            $controlGroup.attr("class", $controlGroup.data("original-classes"));
            // reset aria
            $this.attr("aria-invalid", $this.data("original-aria-invalid"));
            // reset role
            $helpBlock.attr("role", $this.data("original-role"));
						// remove all elements we created
						if (createdElements.indexOf($helpBlock[0]) > -1) {
							$helpBlock.remove();
						}

          }
        );

      },
      collectErrors : function(includeEmpty) {

        var errorMessages = {};
        this.each(function (i, el) {
          var $el = $(el);
          var name = $el.attr("name");
          var errors = $el.triggerHandler("validation.validation", {includeEmpty: true});
          errorMessages[name] = $.extend(true, errors, errorMessages[name]);
        });

        $.each(errorMessages, function (i, el) {
          if (el.length === 0) {
            delete errorMessages[i];
          }
        });

        return errorMessages;

      },
      hasErrors: function() {

        var errorMessages = [];

        this.each(function (i, el) {
          errorMessages = errorMessages.concat(
            $(el).triggerHandler("getValidators.validation") ? $(el).triggerHandler("validation.validation", {submitting: true}) : []
          );
        });

        return (errorMessages.length > 0);
      },
      override : function (newDefaults) {
        defaults = $.extend(true, defaults, newDefaults);
      }
    },
		validatorTypes: {
      callback: {
        name: "callback",
        init: function ($this, name) {
          return {
            validatorName: name,
            callback: $this.data("validation" + name + "Callback"),
            lastValue: $this.val(),
            lastValid: true,
            lastFinished: true
          };
        },
        validate: function ($this, value, validator) {
          if (validator.lastValue === value && validator.lastFinished) {
            return !validator.lastValid;
          }

          if (validator.lastFinished === true)
          {
            validator.lastValue = value;
            validator.lastValid = true;
            validator.lastFinished = false;

            var rrjqbvValidator = validator;
            var rrjqbvThis = $this;
            executeFunctionByName(
              validator.callback,
              window,
              $this,
              value,
              function (data) {
                if (rrjqbvValidator.lastValue === data.value) {
                  rrjqbvValidator.lastValid = data.valid;
                  if (data.message) {
                    rrjqbvValidator.message = data.message;
                  }
                  rrjqbvValidator.lastFinished = true;
                  rrjqbvThis.data("validation" + rrjqbvValidator.validatorName + "Message", rrjqbvValidator.message);
                  // Timeout is set to avoid problems with the events being considered 'already fired'
                  setTimeout(function () {
                    rrjqbvThis.trigger("change.validation");
                  }, 1); // doesn't need a long timeout, just long enough for the event bubble to burst
                }
              }
            );
          }

          return false;

        }
      },
      ajax: {
        name: "ajax",
        init: function ($this, name) {
          return {
            validatorName: name,
            url: $this.data("validation" + name + "Ajax"),
            lastValue: $this.val(),
            lastValid: true,
            lastFinished: true
          };
        },
        validate: function ($this, value, validator) {
          if (""+validator.lastValue === ""+value && validator.lastFinished === true) {
            return validator.lastValid === false;
          }

          if (validator.lastFinished === true)
          {
            validator.lastValue = value;
            validator.lastValid = true;
            validator.lastFinished = false;
            $.ajax({
              url: validator.url,
              data: "value=" + value + "&field=" + $this.attr("name"),
              dataType: "json",
              success: function (data) {
                if (""+validator.lastValue === ""+data.value) {
                  validator.lastValid = !!(data.valid);
                  if (data.message) {
                    validator.message = data.message;
                  }
                  validator.lastFinished = true;
                  $this.data("validation" + validator.validatorName + "Message", validator.message);
                  // Timeout is set to avoid problems with the events being considered 'already fired'
                  setTimeout(function () {
                    $this.trigger("change.validation");
                  }, 1); // doesn't need a long timeout, just long enough for the event bubble to burst
                }
              },
              failure: function () {
                validator.lastValid = true;
                validator.message = "ajax call failed";
                validator.lastFinished = true;
                $this.data("validation" + validator.validatorName + "Message", validator.message);
                // Timeout is set to avoid problems with the events being considered 'already fired'
                setTimeout(function () {
                  $this.trigger("change.validation");
                }, 1); // doesn't need a long timeout, just long enough for the event bubble to burst
              }
            });
          }

          return false;

        }
      },
			regex: {
				name: "regex",
				init: function ($this, name) {
					return {regex: regexFromString($this.data("validation" + name + "Regex"))};
				},
				validate: function ($this, value, validator) {
					value = value.trim();
					return (!validator.regex.test(value) && ! validator.negative)
						|| (validator.regex.test(value) && validator.negative);
				}
			},
			required: {
				name: "required",
				init: function ($this, name) {
					return {};
				},
				validate: function ($this, value, validator) {
					return !!(value.length === 0  && ! validator.negative)
						|| !!(value.length > 0 && validator.negative);
				},
        blockSubmit: true
			},
			match: {
				name: "match",
				init: function ($this, name) {
					var element = $this.parents("form").first().find("[name=\"" + $this.data("validation" + name + "Match") + "\"]").first();
					element.bind("validation.validation", function () {
						$this.trigger("change.validation", {submitting: true});
					});
					return {"element": element};
				},
				validate: function ($this, value, validator) {
					return (value !== validator.element.val() && ! validator.negative)
						|| (value === validator.element.val() && validator.negative);
				},
        blockSubmit: true
			},
			max: {
				name: "max",
				init: function ($this, name) {
					return {max: $this.data("validation" + name + "Max")};
				},
				validate: function ($this, value, validator) {
					return (parseFloat(value, 10) > parseFloat(validator.max, 10) && ! validator.negative)
						|| (parseFloat(value, 10) <= parseFloat(validator.max, 10) && validator.negative);
				}
			},
			min: {
				name: "min",
				init: function ($this, name) {
					return {min: $this.data("validation" + name + "Min")};
				},
				validate: function ($this, value, validator) {
					return (parseFloat(value) < parseFloat(validator.min) && ! validator.negative)
						|| (parseFloat(value) >= parseFloat(validator.min) && validator.negative);
				}
			},
			maxlength: {
				name: "maxlength",
				init: function ($this, name) {
					return {maxlength: $this.data("validation" + name + "Maxlength")};
				},
				validate: function ($this, value, validator) {
					return ((value.length > validator.maxlength) && ! validator.negative)
						|| ((value.length <= validator.maxlength) && validator.negative);
				}
			},
			minlength: {
				name: "minlength",
				init: function ($this, name) {
					return {minlength: $this.data("validation" + name + "Minlength")};
				},
				validate: function ($this, value, validator) {
					return ((value.length < validator.minlength) && ! validator.negative)
						|| ((value.length >= validator.minlength) && validator.negative);
				}
			},
			maxchecked: {
				name: "maxchecked",
				init: function ($this, name) {
					var elements = $this.parents("form").first().find("[name=\"" + $this.attr("name") + "\"]");
					elements.bind("click.validation", function () {
						$this.trigger("change.validation", {includeEmpty: true});
					});
					return {maxchecked: $this.data("validation" + name + "Maxchecked"), elements: elements};
				},
				validate: function ($this, value, validator) {
					return (validator.elements.filter(":checked").length > validator.maxchecked && ! validator.negative)
						|| (validator.elements.filter(":checked").length <= validator.maxchecked && validator.negative);
				},
        blockSubmit: true
			},
			minchecked: {
				name: "minchecked",
				init: function ($this, name) {
					var elements = $this.parents("form").first().find("[name=\"" + $this.attr("name") + "\"]");
					elements.bind("click.validation", function () {
						$this.trigger("change.validation", {includeEmpty: true});
					});
					return {minchecked: $this.data("validation" + name + "Minchecked"), elements: elements};
				},
				validate: function ($this, value, validator) {
					return (validator.elements.filter(":checked").length < validator.minchecked && ! validator.negative)
						|| (validator.elements.filter(":checked").length >= validator.minchecked && validator.negative);
				},
        blockSubmit: true
			}
		},
		builtInValidators: {
			email: {
				name: "Email",
				type: "shortcut",
				shortcut: "validemail"
			},
			validemail: {
				name: "Validemail",
				type: "regex",
				regex: "[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\\.[A-Za-z]{2,4}",
				message: "Not a valid email address<!-- data-validator-validemail-message to override -->"
			},
			passwordagain: {
				name: "Passwordagain",
				type: "match",
				match: "password",
				message: "Does not match the given password<!-- data-validator-paswordagain-message to override -->"
			},
			positive: {
				name: "Positive",
				type: "shortcut",
				shortcut: "number,positivenumber"
			},
			negative: {
				name: "Negative",
				type: "shortcut",
				shortcut: "number,negativenumber"
			},
			number: {
				name: "Number",
				type: "regex",
				regex: "([+-]?\\\d+(\\\.\\\d*)?([eE][+-]?[0-9]+)?)?",
				message: "Must be a number<!-- data-validator-number-message to override -->"
			},
			integer: {
				name: "Integer",
				type: "regex",
				regex: "[+-]?\\\d+",
				message: "No decimal places allowed<!-- data-validator-integer-message to override -->"
			},
			positivenumber: {
				name: "Positivenumber",
				type: "min",
				min: 0,
				message: "Must be a positive number<!-- data-validator-positivenumber-message to override -->"
			},
			negativenumber: {
				name: "Negativenumber",
				type: "max",
				max: 0,
				message: "Must be a negative number<!-- data-validator-negativenumber-message to override -->"
			},
			required: {
				name: "Required",
				type: "required",
				message: "This is required<!-- data-validator-required-message to override -->"
			},
			checkone: {
				name: "Checkone",
				type: "minchecked",
				minchecked: 1,
				message: "Check at least one option<!-- data-validation-checkone-message to override -->"
			}
		}
	};

	var formatValidatorName = function (name) {
		return name
			.toLowerCase()
			.replace(
				/(^|\s)([a-z])/g ,
				function(m,p1,p2) {
					return p1+p2.toUpperCase();
				}
			)
		;
	};

	var getValue = function ($this) {
		// Extract the value we're talking about
		var value = $this.val();
		if(value == null){
			return '';
		}
		var type = $this.attr("type");
		if (type === "checkbox") {
			value = ($this.is(":checked") ? value : "");
		}
		if (type === "radio") {
			value = ($('input[name="' + $this.attr("name") + '"]:checked').length > 0 ? value : "");
		}
		return value;
	};

  function regexFromString(inputstring) {
		return new RegExp("^" + inputstring + "$");
	}

  /**
   * Thanks to Jason Bunting via StackOverflow.com
   *
   * http://stackoverflow.com/questions/359788/how-to-execute-a-javascript-function-when-i-have-its-name-as-a-string#answer-359910
   * Short link: http://tinyurl.com/executeFunctionByName
  **/
  function executeFunctionByName(functionName, context /*, args*/) {
    var args = Array.prototype.slice.call(arguments).splice(2);
    var namespaces = functionName.split(".");
    var func = namespaces.pop();
    for(var i = 0; i < namespaces.length; i++) {
      context = context[namespaces[i]];
    }
    return context[func].apply(this, args);
  }

	$.fn.jqBootstrapValidation = function( method ) {

		if ( defaults.methods[method] ) {
			return defaults.methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
			return defaults.methods.init.apply( this, arguments );
		} else {
		$.error( 'Method ' +  method + ' does not exist on jQuery.jqBootstrapValidation' );
			return null;
		}

	};

  $.jqBootstrapValidation = function (options) {
    $(":input").not("[type=image],[type=submit]").jqBootstrapValidation.apply(this,arguments);
  };

  //Document ready
  $(document).ready(function(){
	  $('.rb-validate-form').find("input,textarea,select").not('.no-validate').jqBootstrapValidation();
  });
  
})( rb.jQuery );


/**
 *
 * ############################################################################################
 * ######################### 		Rb Credit Card namespace		 ##########################
 * ############################################################################################
 * 
 * 
 * Check Credit Card Validation 
 * Inspired by: https://github.com/stripe/jquery.payment
 *
 * @package     Rb_pkg
 * @since       1.1
 * @author mManishTrivedi
 * 
 */
var rb_credit_card = function() 
 { 
	var rb_default_card_format = /(\d{1,4})/g; // Credit card format
	
	// Available Credit cards
	var cards = 
					[					 
	 					{
	 						type: 'visaelectron',
	 						pattern: /^4(026|17500|405|508|844|91[37])/,
	 						format: rb_default_card_format,
	 						length: [16],
	 						cvcLength: [3],
	 						luhn: true
	 					}, {
	 						type: 'maestro',
	 						pattern: /^(5(018|0[23]|[68])|6(39|7))/,
	 						format: rb_default_card_format,
	 						length: [12, 13, 14, 15, 16, 17, 18, 19],
	 						cvcLength: [3],
	 						luhn: true
	 					}, {
	 						type: 'forbrugsforeningen',
	 						pattern: /^600/,
	 						format: rb_default_card_format,
	 						length: [16],
	 						cvcLength: [3],
	 						luhn: true
	 					}, {
	 						type: 'dankort',
	 						pattern: /^5019/,
	 						format: rb_default_card_format,
	 						length: [16],
	 						cvcLength: [3],
	 						luhn: true
	 					}, {
	 						type: 'visa',
	 						pattern: /^4/,
	 						format: rb_default_card_format,
	 						length: [13, 16],
	 						cvcLength: [3],
	 						luhn: true
	 					}, {
	 						type: 'mastercard',
	 						pattern: /^5[0-5]/,
	 						format: rb_default_card_format,
	 						length: [16],
	 						cvcLength: [3],
	 						luhn: true
	 					}, {
	 						type: 'amex',
	 						pattern: /^3[47]/,
	 						format: /(\d{1,4})(\d{1,6})?(\d{1,5})?/,
	 						length: [15],
	 						cvcLength: [3, 4],
	 						luhn: true
	 					}, {
	 						type: 'dinersclub',
	 						pattern: /^3[0689]/,
	 						format: rb_default_card_format,
	 						length: [14],
	 						cvcLength: [3],
	 						luhn: true
	 					}, {
	 						type: 'discover',
	 						pattern: /^6([045]|22)/,
	 						format: rb_default_card_format,
	 						length: [16],
	 						cvcLength: [3],
	 						luhn: true
	 					}, {
	 						type: 'unionpay',
	 						pattern: /^(62|88)/,
	 						format: rb_default_card_format,
	 						length: [16, 17, 18, 19],
	 						cvcLength: [3],
	 						luhn: false
	 					}, {
	 						type: 'jcb',
	 						pattern: /^35/,
	 						format: rb_default_card_format,
	 						length: [16],
	 						cvcLength: [3],
	 						luhn: true
	 					}
	 					];
	/**
	 *  Invoke to get Card 
	 *  
	 * @param Numric Value 
	 * @returns card object if exist
	 */
	var getCard = 
			function( number)
			{
				var card, _i, _len;
				
				// \D :: replace any character that's not a digit [^0-9]
				// /g :: (modifier)global. All matches (don't return on first match)
				number = (number + '').replace(/\D/g, '');
				
				for (_i = 0, _len = cards.length; _i < _len; _i++) {
					card = cards[_i];
					if (card.pattern.test(number)) {
						return card;
					}
				}
				return null;
			},
			
			
	/**
	 * Invoke to get Card-type 
	 *  
	 * @param Numric Value 
	 * @returns card-type if exist
	 */			
	 getType = 
			function( number)
			{
				var _ref;
				if (!number) {
					return null;
				}
				
				return  (_ref = getCard(number)) != null ? _ref.type : null;
			},
	
	/**
	 * Invoke to check card-number is validate or not.
	 * 	luhn Checksum Algos apply
	 * 
	 * @param Nemeric Number 
	 * 
	 * @return (bool) true if valid number 
	 */
	isValidLuhn = function(num) 
			{
				 var digit, digits, odd, sum, _i, _len;
				 odd = true;
				 sum = 0;
				 digits = (num + '').split('').reverse();
				 
				 for (_i = 0, _len = digits.length; _i < _len; _i++) {
					 digit = digits[_i];
					 digit = parseInt(digit, 10);
					 
					 if ((odd = !odd)) {
						 digit *= 2;
					 }
					 
					 if (digit > 9) {
						 digit -= 9;
					 }
				 
					 sum += digit;
				 }
				 
				 return sum % 10 === 0;
			};
			
			
	return {
				getCard		:	getCard,	
				getType 	: 	getType,
				isValidLuhn	:	isValidLuhn,
			
			};
 }
	 			
/**
 * ############################################################################################
 * ########################### 		Rb Form Validation		 ##################################
 * ############################################################################################
 * 
 * Unobtrusive Form Validation library
 * Inspired by: Chris Campbell <www.particletree.com>
 *
 * @package     Rb_pkg
 * @since       1.1
 */
var Rb_FormValidator = function() {
	var $, handlers, inputEmail, custom,

 	setHandler = function(name, fn, en) {
 	 	en = (en === '') ? true : en;
 	 	handlers[name] = {
 	 	 	enabled : en,
 	 	 	exec : fn
 	 	};
 	},

 	findLabel = function(id, form){
 	 	var $label, $form = jQuery(form);
 	 	if (!id) {
 	 	 	return false;
 	 	}
 	 	$label = $form.find('#' + id + '-lbl');
 	 	if ($label.length) {
 	 	 	return $label;
 	 	}
 	 	$label = $form.find('label[for="' + id + '"]');
 	 	if ($label.length) {
 	 	 	return $label;
 	 	}
 	 	return false;
 	},

 	handleResponse = function(state, $el, type, msg) {
 		
 		if ( $el.attr('data-rb-validate-error') ) {
 			var $error = $($el.attr('data-rb-validate-error'));
 			var $label = false;
 		} else if($el.attr('id')) {
 			var $label = $el.data('label');
 			var $error = $('[for="'+$el.attr('id')+'"]').not($label);
 		} else {
 			var $error = $('[for="'+$el.selector+'"]');
 			var $label = false; 
 		}
 		
 		type = typeof type !== 'undefined' ? type : 'error';
 		msg = typeof msg !== 'undefined' ? msg : $el.attr('error-message');
 		

 	 	// Set the element and its label (if exists) invalid state
 	 	if (state === false) {
 	 	 	$el.addClass('invalid').attr('aria-invalid', 'true'); 	 	 	
 	 	 	if($error){
 	 	 		$error.removeClass('hide');
 	 	 		$error.addClass('show').html(msg);
 	 	 	}
 	 	 	if($label){
 	 	 	 	$label.addClass('invalid').attr('aria-invalid', 'true');
 	 	 	}
 	 	} else {
 	 	 	$el.removeClass('invalid').attr('aria-invalid', 'false'); 	 	 	
 	 	 	if($error){
 	 	 		$error.removeClass('show');
 	 	 		$error.addClass('hide')
 	 	 	}
 	 	 	if ($label) {
 	 	 	 	$label.removeClass('invalid').attr('aria-invalid', 'false');
 	 	 	}
 	 	}
 	},

 	validate = function(el) {
 	 	var $el = jQuery(el), tagName, handler;
 	 	// Ignore the element if its currently disabled, because are not submitted for the http-request. For those case return always true.
 	 	if ($el.attr('disabled')) {
 	 	 	handleResponse(true, $el);
 	 	 	return true;
 	 	}
 	 	// If the field is required make sure it has a value
 	 	if ($el.attr('required') || $el.hasClass('required')) {
 	 	 	tagName = $el.prop("tagName").toLowerCase();
 	 	 	if (tagName === 'fieldset' && ($el.hasClass('radio') || $el.hasClass('checkboxes'))) {
 	 	 	 	if (!$el.find('input:checked').length){
 	 	 	 	 	handleResponse(false, $el);
 	 	 	 	 	return false;
 	 	 	 	}
 	 	 	//If element has class placeholder that means it is empty.
 	 	 	} else if (!$el.val() || $el.hasClass('placeholder') || ($el.attr('type') === 'checkbox' && !$el.is(':checked'))) {
 	 	 	 	handleResponse(false, $el);
 	 	 	 	return false;
 	 	 	}
 	 	}
 	 	// Only validate the field if the validate class is set
 	 	handler = ($el.attr('class') && $el.attr('class').match(/validate-([a-zA-Z0-9\_\-]+)/)) ? $el.attr('class').match(/validate-([a-zA-Z0-9\_\-]+)/)[1] : "";
 	 	if (handler === '') {
 	 	 	handleResponse(true, $el);
 	 	 	return true;
 	 	}
 	 	// Check the additional validation types
 	 	if ((handler) && (handler !== 'none') && (handlers[handler]) && $el.val()) {
 	 	 	// Execute the validation handler and return result
 	 	 	if (handlers[handler].exec($el, $el.val()) !== true) {
 	 	 	 	handleResponse(false, $el);
 	 	 	 	return false;
 	 	 	}
 	 	}
 	 	// Return validation state
 	 	handleResponse(true, $el);
 	 	return true;
 	},

 	isValid = function(form) {
 	 	var valid = true, i, message, errors, error, label;
 	 	// Validate form fields
 	 	jQuery.each(jQuery(form).find('input, textarea, select, fieldset, button'), function(index, el) {
 	 	 	if (($(el).is(':visible') || $(el).hasClass('validate-hidden'))&& validate(el) === false) {
 	 	 	 	valid = false;
 	 	 	}
 	 	});
 	 	// Run custom form validators if present
 	 	jQuery.each(custom, function(key, validator) {
 	 	 	if (validator.exec() !== true) {
 	 	 	 	valid = false;
 	 	 	}
 	 	});
 	 	if (!valid) {
 	 	 	//message = Joomla.JText._('JLIB_FORM_FIELD_INVALID');
 	 	 	
 	 	 	scrollToError(form);
 	 	 	
// 	 	 	Joomla.renderMessages(error);
 	 	}	 	
 	 	
 	 	return valid;
 	},
 	
 	scrollToError = function(form){
 		errors = jQuery(form).find("input.invalid, textarea.invalid, select.invalid, fieldset.invalid, button.invalid");
 	 	 	
 	 	var el = $(errors[0]);

 	 	if($(el).hasClass('validate-hidden')){
 	 		//if hidden element then calculate offset of its error element 	 		
 	 		var elOffset = $('[for="'+el.attr('id')+'"]').offset().top;
 	 	}else{
 	 		var elOffset = el.offset().top;
 	 	}
		
 	    var elHeight = el.height();
 	    var windowHeight = $(window).height();
 	    var offset;

 	    if (elHeight < windowHeight) {
 	    	offset = elOffset - ((windowHeight / 2) - (elHeight / 2));
 	    }
 	    else {
 	    	offset = elOffset;
 	    }
 	  
 	 	$('html, body').animate({
 	        scrollTop: offset
 	    }, 1000);
 	},
 	
 	attachToForm = function(form) {
 	 	var inputFields = [];
 	 	// Iterate through the form object and attach the validate method to all input fields.
 	 	$(form).find('input, textarea, select, fieldset, button').each(function() {
 	 	 	var $el = $(this), id = $el.attr('id'), tagName = $el.prop("tagName").toLowerCase();
 	 	 	if(!$el.is(':visible') && !$el.hasClass('validate-hidden')){
 	 	 		return true;
 	 	 	}
 	 	 	if ($el.hasClass('required')) {
 	 	 	 	$el.attr('aria-required', 'true').attr('required', 'required');
 	 	 	}
 	 	 	if ((tagName === 'input' || tagName === 'button') && $el.attr('type') === 'submit') {
 	 	 	 	if ($el.hasClass('validate')) {
 	 	 	 	 	$el.on('click', function() {
 	 	 	 	 	 	return isValid(form);
 	 	 	 	 	});
 	 	 	 	}
 	 	 	} else {
				//in case of chosen
 	 	 		if($el.hasClass('validate-hidden')){
 	 	 			$el.on('change', function() {
 	 	 	 	 	 	return validate(this);
 	 	 	 	 	});
 	 	 		}
 	 	 	 	if (tagName !== 'fieldset') {
 	 	 	 	 	$el.on('blur', function() {
 	 	 	 	 	 	return validate(this);
 	 	 	 	 	});
 	 	 	 	 	if ($el.hasClass('validate-email') && inputEmail) {
 	 	 	 	 	 	$el.get(0).type = 'email';
 	 	 	 	 	}
 	 	 	 	}
 	 	 	 	$el.data('label', findLabel(id, form));
 	 	 	 	inputFields.push($el);
 	 	 	}
 	 	});
 	 	$(form).data('inputfields', inputFields);
 	},

 	initialize = function(selector) {
 	 	$ = jQuery.noConflict();
 	 	handlers = {};
 	 	custom = custom || {};

 	 	inputEmail = (function() {
 	 	 	var input = document.createElement("input");
 	 	 	input.setAttribute("type", "email");
 	 	 	return input.type !== "text";
 	 	})();
 	 	// Default handlers
 	 	setHandler('username', function(element, value) {
 	 	 	regex = new RegExp("[\<|\>|\"|\'|\%|\;|\(|\)|\&]", "i");
 	 	 	return !regex.test(value);
 	 	});
 	 	setHandler('password', function(element, value) {
 	 	 	regex = /^\S[\S ]{2,98}\S$/;
 	 	 	return regex.test(value);
 	 	});
 	 	setHandler('numeric', function(element, value) {
 	 	 	regex = /^(\d|-)?(\d|,)*\.?\d*$/;
 	 	 	return regex.test(value);
 	 	});
 	 	setHandler('integer', function(element, value) {
 	 	 	regex = /^(\d|-)?(\d|,)*\d*$/;
 	 	 	return regex.test(value);
 	 	});
 	 	setHandler('email', function(element, value) {
 	 	 	regex = /^[a-zA-Z0-9.!#$%&‚Äô*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
 	 	 	return regex.test(value);
 	 	});
 	 	setHandler('image', function (element, value) {
			var imageSize  = 0;
			var fileField = element[0]; //as element is an object
			for (i = 0; i < fileField.files.length; i++){
				  //inputField.files[0].size gets the size of your file.
				  imageSize +=  fileField.files[i].size;
			}
			return (element.data('fileuploadlimit') > imageSize); 
	    });
		
		/*
		 * handler to validate minimum numeric value
		 * IMP: Element should have a minimum numeric value in data attribute 'data-rb-min'
		 */
 	 	setHandler('min', function (element, value) {
 	 		regex = /^(\d|-)?(\d|,)*\.?\d*$/;
			return (regex.test(value) && value > element.data('rb-min')); 
	    });
 	 	
 	 	/*
 	 	 * Auto invoke when html element have 'validate-rb-credit-card' class
 	 	 *  Validation rule apply according to available creadit cards type 
 	 	 */
 	 	setHandler('rb-credit-card', function (element, value) {
 	 		
 	 		//clean value
 	 		value = (value + '').replace(/\D/g, '');
 	 		 
 	 		//Validation-1: get card is exist or not
 	 		var rb_card = new rb_credit_card();
 	 		
 	 		var card = rb_card.getCard(value);
 	 		
 	 		// Error : Card is not exist
 	 		if (!card) {
 	 			return false;
 	 		}
 	 		
 	 		//Validation-2 : Check card-number length
 	 		if( -1 == rb.jQuery.inArray(value.length, card.length) ) {
 	 			return false;
 	 		}

 	 		//Validation-3 : Checksum checking, Validate card number
 	 		if (!rb_card.isValidLuhn(value)) {
 	 			return false;
 	 		}
 	 		
			return true; 
	    });
 	 	
 	 	/*
 	 	 * Invoke to validate cvc length according to credit card
 	 	 * 	- Field must have data attribute "data-rb-validate", This is a creadit-card field. 
 	 	 *  
 	 	 */
 	 	setHandler('rb-cvc-length', function (element, value) {
 	 		
 	 		var card_number,
 	 			card_selector = element.data('rb-validate'),	// get card selector 
 	 			rb_card = new rb_credit_card();
 	 		
 	 		// get cart number
 	 		card_number = rb.jQuery(card_selector).val();
 	 		
 	 		// if card-number is not exit
 	 		if (!card_number) {
 	 			//error :: card is not exist
 	 			return true;
 	 		}
 	 		
 	 		//get card is exist or not
 	 		var card = rb_card.getCard(card_number);
 	 		
 	 		// Validation will not fire if card is not exist 
 	 		if (!card) { 
 	 			//error :: card is not valid 
 	 			return true;
 	 		}
 	 		
 	 		//Validation-1 : cvc length according to card cvc length
 	 		if( -1 == rb.jQuery.inArray(value.length, card.cvcLength) ) {
 	 			return false;
 	 		}
 	 		
 	 		return true; 
	    });
 	 	
 	 	/*
 	 	 *  Invoke to validate any field accoring to regex
 	 	 *  	- field must have regx patterm(data-rb-validate-pattern)
 	 	 *  	- like you want to field have mm/yy value then add data-rb-validate-pattern="^(0[1-9]|1[0-2])/([1-9][4-9])$" as field-attribute
 	 	 *  	
 	 	 *  
 	 	 */
 	 	setHandler('rb-regex-pattern', function (element, value) {
 	 		
 	 		var regex_pattern,  regex_pattern_string = element.data('validate-pattern');
 	 		
 	 		if (! regex_pattern_string ) {
 	 			// Addtribute "data-rb-validate-pattern" is not define 
 	 			return true;
 	 		}
 	 		//get regex object
 	 		regex_pattern = new RegExp(regex_pattern_string );
 	 		
 	 		return regex_pattern.test(value); 
	    });
 	 	
 	 	/*
 	 	 *  Invoke to validate expiry date with current date 
 	 	 *  	- Two fields will use
 	 	 *  	- data-rb-validate-type attribute must be required for this validation 
 	 	 *  	- data-rb-validate-type have either 'year' or 'month' value. 
 	 	 *  	
 	 	 */
 	 	setHandler('rb-exp-date', function (element, value) {
 	 		
 	 		var month_number, year_number, date, mm, yyyy, month_el, year_el;
 	 		
 	 		var l = element.data('rb-validate-type');
 	 		
 	 		// element find who am I? (Either month-element or year-element )
 	 		if ( 'month' === element.data('rb-validate-type') ) {
 	 			month_el 	= 	element;
 	 			year_el		=	rb.jQuery(element.data('rb-validate'));
 	 		} else {
 	 			year_el 	= 	element;
 	 			month_el	=	rb.jQuery(element.data('rb-validate'));
 	 		}
 	 		
 	 		month_number	= month_el.val();
 	 		year_number		= year_el.val();
 	 		
 	 		if (!month_number && !year_number) {
 	 			return false 
 	 		}
 	 		
 	 		date = new Date();

 	 		mm 		= date.getMonth()+1; // start with 0 ie January is 0! so add 1
 	 		yyyy 	= date.getFullYear();

 	 		year_number	 = parseInt(year_number);
 	 		month_number = parseInt(month_number);
 	 		
 	 		// if input year-number is less than current year then return false 
 	 		if ( year_number < yyyy ) {
 	 			return false;
 	 		}
 	 		
 	 		// if curent year and input year number same then comapre month 
 	 		if (year_number == yyyy && month_number <= mm ) {
 	 			return false;
 	 		}

 	 		// if everything is ok then call manually handle-response
 	 		// (because both elements are diffrent, So we need manually invoke handle response )
 	 		handleResponse(true, month_el);
 	 		handleResponse(true, year_el);
 	 		
 	 		return true;
 	 		 
	    });
 	 	
 	 	// Attach to forms with class 'form-validate'
 	 	jQuery(selector).each(function() {
 	 	 	attachToForm(this);
 	 	}, this);
 	};
 
 	return {
 		initialize : initialize,
 	 	isValid : isValid,
 	 	validate : validate,
 	 	setHandler : setHandler,
 	 	attachToForm : attachToForm,
 	 	custom: custom,
 	 	handleResponse : handleResponse,
 	 	scrollToError : scrollToError
 	};
};

