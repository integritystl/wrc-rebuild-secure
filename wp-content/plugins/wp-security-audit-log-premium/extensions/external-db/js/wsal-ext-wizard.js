( function( $ ) {

	$( document ).ready( function() {

		// Current wizard step.
		var currentStep = 1;
		var wizardSave = jQuery( '#wizard-save' );
		var wizardNext = jQuery( '#wizard-next' );
		var wizardCancel = jQuery( '#wizard-cancel' );
		var configSaveBtn = '';

		if ( '' !== scriptData.connection || '' !== scriptData.mirror ) {
			configSaveBtn = jQuery( '#submit' );
		}

		// initalise the dialog
		function initializeWizard( wizardId, wizardTitle ) {
			$( wizardId ).dialog({
				title: wizardTitle,
				dialogClass: 'wp-dialog',
				autoOpen: false,
				draggable: false,
				width: 750,
				modal: true,
				resizable: false,
				closeOnEscape: true,
				position: {
					my: 'center',
					at: 'center',
					of: window
				},
				open: function() {

					// close dialog by clicking the overlay behind it
					$( '.ui-widget-overlay' ).bind( 'click', function() {
						$( wizardId ).dialog( 'close' );
					});
					wizardCancel.bind( 'click', function() {
						$( wizardId ).dialog( 'close' );
					});
				},
				create: function() {

					// style fix for WordPress admin
					$( '.ui-dialog-titlebar-close' ).addClass( 'ui-button' );
				}
			});
		}

		function bindWizardBtn( btnId, wizardId ) {
			$( btnId ).click( function( e ) {
				e.preventDefault();
				$( wizardId ).dialog( 'open' );
				resetConnectionWizard();

				if ( scriptData.connection ) {
					resetWizardFields();
				}

				loadNextWizardStep( currentStep );
			});
		}

		initializeWizard( '#wsal-connection-wizard', scriptData.title ); // Connection Wizard.
		initializeWizard( '#wsal-mirroring-wizard', scriptData.mirrorTitle ); // Mirroring Wizard.
		bindWizardBtn( '#wsal-create-connection', '#wsal-connection-wizard' );
		bindWizardBtn( '#wsal-create-mirror', '#wsal-mirroring-wizard' );

		// Edit Connection.
		if ( scriptData.connection ) {
			$( '#wsal-connection-wizard' ).dialog( 'open' );
			resetConnectionWizard();
			loadNextWizardStep( currentStep );
		}

		// Edit Mirror.
		if ( scriptData.mirror ) {
			$( '#wsal-mirroring-wizard' ).dialog( 'open' );
			resetConnectionWizard();
			loadNextWizardStep( currentStep );
		}

		/**
		 * Reset Connection Wizard Contents.
		 */
		function resetConnectionWizard() {
			currentStep = 1;
			jQuery( '#content-step-1' ).addClass( 'hide' );
			jQuery( '#content-step-2' ).addClass( 'hide' );
			jQuery( '#content-step-2 > div' ).addClass( 'hide' );
			jQuery( '#content-step-3' ).addClass( 'hide' );
			jQuery( '#wizard-save' ).addClass( 'hide' );
			jQuery( '#wizard-next' ).addClass( 'hide' );
			jQuery( '#wizard-cancel' ).addClass( 'hide' );
			jQuery( '.steps > li' ).removeClass( 'is-active' );
		}

		/**
		 * Load Next Connection Wizard Step.
		 *
		 * @param {integer} currentStep – Current step number.
		 */
		function loadNextWizardStep( currentStep ) {
			var previousStep = currentStep - 1;
			var connectionType = jQuery( '#connection-type' ).val();

			jQuery( '#step-' + previousStep ).removeClass( 'is-active' );
			jQuery( '#content-step-' + previousStep ).addClass( 'hide' );
			jQuery( '#step-' + currentStep ).addClass( 'is-active' );
			jQuery( '#content-step-' + currentStep ).removeClass( 'hide' );

			if ( 1 === currentStep ) {
				wizardNext.removeClass( 'hide' );
				wizardCancel.removeClass( 'hide' );
			} else if ( 2 === currentStep ) {
				jQuery( '.details-' + connectionType ).removeClass( 'hide' );
			} else if ( 3 === currentStep ) {
				wizardNext.addClass( 'hide' );
				wizardSave.removeClass( 'hide' );
			}

		}

		wizardNext.click( function( e ) {
			var connectionType = '';
			var detailInputs   = '';

			var index = 0;
			var count = 0;
			var field = '';
			e.preventDefault(); // Prevent default.
			earlyconnectionType = jQuery( '#connection-type' ).val();
			if ( 'mysql' === earlyconnectionType ) {
				jQuery( '#wizard-next' ).val( scriptData.testContinue );
			}
			// Check for required fields in connection details form.
			if ( 2 === currentStep ) {
				connectionType = jQuery( '#connection-type' ).val();
				detailInputs   = jQuery( '.details-' + connectionType + ' input[data-type=required]' );

				for ( index = 0; index < detailInputs.length; index++ ) {
					field = jQuery( detailInputs[index]);

					if ( checkInputForError( field ) ) {
						count++;
					}
				}

				if ( 0 !== count ) {
					return;
				}

				if ( 'mysql' === connectionType ) {
					jQuery( '.wizard-error' ).addClass( 'hide' );
					// disable the button during test.
					jQuery( '#wizard-next' ).attr( 'disabled', true );
					jQuery( '#test-spinner' ).addClass( 'is-active' );
					jQuery( '#test-spinner' ).show();

					// Ajax request to test connection.
					jQuery.ajax({
						type: 'POST',
						url: scriptData.ajaxURL,
						async: true,
						dataType: 'json',
						data: {
							action: 'wsal_wizard_connection_test',
							wpnonce: jQuery( '#wsal-connection-wizard #_wpnonce' ).val(),
							dbName: jQuery( 'input[name="connection[mysql][dbName]"]' ).val(),
							dbUser: jQuery( 'input[name="connection[mysql][dbUser]"]' ).val(),
							dbPassword: jQuery( 'input[name="connection[mysql][dbPassword]"]' ).val(),
							dbHostname: jQuery( 'input[name="connection[mysql][dbHostname]"]' ).val(),
							dbBasePrefix: jQuery( 'input[name="connection[mysql][dbBasePrefix]"]' ).val(),
							dbUrlBasePrefix: jQuery( 'input[name="connection[mysql][dbUrlBasePrefix]"]' ).is( ':checked' ),
							dbSSL: jQuery( 'input[name="connection[mysql][dbSSL]"]' ).is( ':checked' ),
							sslCC: jQuery( 'input[name="connection[mysql][sslCC]"]' ).is( ':checked' ),
							sslCA: jQuery( 'input[name="connection[mysql][sslCA]"]' ).val(),
							sslCert: jQuery( 'input[name="connection[mysql][sslCert]"]' ).val(),
							sslKey: jQuery( 'input[name="connection[mysql][sslKey]"]' ).val()
						},
						success: function( data ) {
							// reenable the button.
							jQuery( '#wizard-next' ).attr( 'disabled', false );
							jQuery( '#test-spinner' ).removeClass( 'is-active' );
							jQuery( '#test-spinner' ).hide();
							if ( false === data.success ) {
								jQuery( '.wizard-error' ).removeClass( 'hide' );
								jQuery( '.wizard-error' ).text( data.message );
							} else {
								currentStep += 1;
								loadNextWizardStep( currentStep );
							}
						},
						error: function( xhr, textStatus, error ) {
							// reenable the button.
							jQuery( '#wizard-next' ).attr( 'disabled', false );
							jQuery( '#test-spinner' ).removeClass( 'is-active' );
							jQuery( '#test-spinner' ).hide();
							console.log( xhr.statusText );
							console.log( textStatus );
							console.log( error );
						}
					});
				} else {
					currentStep += 1;
					loadNextWizardStep( currentStep );
				}
			} else if ( 1 === currentStep ) {
				detailInputs = jQuery( '#mirror-name' );
				mirrorConnection = jQuery( '#mirror-connection' );

				if ( checkInputForError( detailInputs ) || checkInputForError( mirrorConnection ) ) {
					return;
				}

				currentStep += 1;
				loadNextWizardStep( currentStep );
			} else {
				currentStep += 1;
				loadNextWizardStep( currentStep );
			}
		});

		/**
		 * Check Input for Error.
		 *
		 * @param {element} detailInput - Wizard HTML Element.
		 */
		function checkInputForError( detailInput ) {
			if ( '' === detailInput.val() || null === detailInput.val() ) {
				detailInput.addClass( 'error' );
				detailInput.change( function() {
					if ( '' !== jQuery( this ).val() && jQuery( this ).hasClass( 'error' ) ) {
						jQuery( this ).removeClass( 'error' );
					}
				});
				return true;
			}
			return false;
		}

		/**
		 * URL Base Prefix.
		 */
		jQuery( '#db-url-base-prefix' ).change( function() {
			if ( jQuery( this ).is( ':checked' ) ) {
				jQuery( '#db-base-prefix' ).attr( 'disabled', true );
				jQuery( '#db-base-prefix' ).val( scriptData.urlBasePrefix );
			} else {
				jQuery( '#db-base-prefix' ).attr( 'disabled', false );
				jQuery( '#db-base-prefix' ).val( '' );
			}
		});

		/**
		 * Reset Wizard Fields.
		 */
		function resetWizardFields() {
			jQuery( '#wsal-connection-wizard input[type="text"]' ).removeAttr( 'value' );
			jQuery( '#wsal-connection-wizard input[type="password"]' ).removeAttr( 'value' );
			jQuery( '#wsal-connection-wizard input[type="checkbox"]' ).removeAttr( 'value' );
			jQuery( '#wsal-connection-wizard select' ).removeAttr( 'value' );
		}

		/**
		 * Delete Connection.
		 */
		jQuery( '.wsal-conn-delete' ).click( function( e ) {
			var connection = '';
			var nonce = '';
			e.preventDefault();

			if ( ! confirm( scriptData.confirm ) ) {
				return;
			}

			connection = jQuery( this ).data( 'connection' );
			nonce = jQuery( this ).data( 'nonce' );
			jQuery( this ).text( scriptData.deleting );

			// Ajax request to test connection.
			jQuery.ajax({
				type: 'POST',
				url: scriptData.ajaxURL,
				async: true,
				dataType: 'json',
				data: {
					action: 'wsal_delete_connection',
					nonce: nonce,
					connection: connection
				},
				success: function( data ) {
					if ( data.success ) {
						location.reload();
					} else {
						console.log( data.message );
					}
				},
				error: function( xhr, textStatus, error ) {
					jQuery( btn ).val( 'Connection Failed!' );
					console.log( xhr.statusText );
					console.log( textStatus );
					console.log( error );
				}
			});
		});

		/**
		 * Test Connection.
		 */
		jQuery( '.wsal-conn-test' ).click( function( e ) {
			var connection = '';
			var nonce = '';
			e.preventDefault();

			testBtn = jQuery( this );
			connection = testBtn.data( 'connection' );
			nonce = testBtn.data( 'nonce' );
			testBtn.text( scriptData.connTest );

			// Ajax request to test connection.
			jQuery.ajax({
				type: 'POST',
				url: scriptData.ajaxURL,
				async: true,
				dataType: 'json',
				data: {
					action: 'wsal_connection_test',
					nonce: nonce,
					connection: connection
				},
				success: function( data ) {
					if ( data.success ) {
						if ( data.customMessage ) {
							testBtn.text( data.customMessage );
						} else {
							testBtn.text( scriptData.connSuccess );
						}
					} else {
						testBtn.text( scriptData.connFailed );
						console.log( data.message );
					}
				},
				error: function( xhr, textStatus, error ) {
					jQuery( testBtn ).text( scriptData.connFailed );
					console.log( xhr.statusText );
					console.log( textStatus );
					console.log( error );
				}
			});
		});

		/**
		 * Mirror Wizard Events.
		 */
		function initializeEventSelect2( selectId, optionId ) {
			if ( 'undefined' !== typeof Select2 && 'object' === typeof Select2 ) {
				$( selectId ).select2({
					placeholder: scriptData.eventsPlaceholder,
					allowClear: true,
					width: '600px'
				})
					.on( 'select2-open', function( e ) {
						var v = $( e ).val;
						if ( v.length ) {
							$( optionId ).prop( 'checked', true );
						}
					}).on( 'select2-selecting', function( e ) {
						var v = $( e ).val;
						if ( v.length ) {
							$( optionId ).prop( 'checked', true );
						}
					}).on( 'select2-removed', function( e ) {
						var v = $( this ).val();
						if ( ! v.length ) {
							$( optionId ).prop( 'checked', false );
						}
					});
			}
		}
		initializeEventSelect2( '#mirror-select-event-codes', '#mirror-filter-event-codes' );
		initializeEventSelect2( '#mirror-select-except-codes', '#mirror-filter-except-codes' );

		/**
		 * Disable Mirror.
		 */
		jQuery( '.wsal-mirror-toggle' ).click( function( event ) {
			var mirrorName = '';
			var toggleNonce = '';
			var toggleBtn = '';
			var mirrorState = '';
			event.preventDefault();

			toggleBtn = jQuery( this );
			mirrorName = toggleBtn.data( 'mirror' );
			toggleNonce = toggleBtn.data( 'nonce' );
			mirrorState = toggleBtn.data( 'state' );

			if ( 'Enable' === toggleBtn.text() ) {
				toggleBtn.text( scriptData.enabling );
			} else {
				toggleBtn.text( scriptData.disabling );
			}

			// Ajax request to test connection.
			jQuery.ajax({
				type: 'POST',
				url: scriptData.ajaxURL,
				async: true,
				dataType: 'json',
				data: {
					action: 'wsal_toggle_mirror_state',
					nonce: toggleNonce,
					mirror: mirrorName,
					state: mirrorState
				},
				success: function( data ) {
					if ( data.success ) {
						toggleBtn.text( data.button );
						toggleBtn.data( 'state', data.state );
					} else {
						console.log( data.message );
					}
				},
				error: function( xhr, textStatus, error ) {
					toggleBtn.val( 'Failed!' );
					console.log( xhr.statusText );
					console.log( textStatus );
					console.log( error );
				}
			});
		});

		/**
		 * Disable Mirror.
		 */
		jQuery( '.wsal-mirror-delete' ).click( function( event ) {
			var mirrorName = '';
			var deleteMirrorNonce = '';
			var deleteMirrorBtn = '';
			event.preventDefault();

			// Delete confirmation.
			if ( ! confirm( scriptData.confirmDelMirror ) ) {
				return;
			}

			deleteMirrorBtn = jQuery( this );
			deleteMirrorBtn.text( scriptData.deleting );
			mirrorName = deleteMirrorBtn.data( 'mirror' );
			deleteMirrorNonce = deleteMirrorBtn.data( 'nonce' );

			// Ajax request to test connection.
			jQuery.ajax({
				type: 'POST',
				url: scriptData.ajaxURL,
				async: true,
				dataType: 'json',
				data: {
					action: 'wsal_delete_mirror',
					nonce: deleteMirrorNonce,
					mirror: mirrorName
				},
				success: function( data ) {
					if ( data.success ) {
						location.reload();
					} else {
						console.log( data.message );
					}
				},
				error: function( xhr, textStatus, error ) {
					deleteMirrorBtn.val( 'Failed!' );
					console.log( xhr.statusText );
					console.log( textStatus );
					console.log( error );
				}
			});
		});

		// Connection name pattern detection.
		$( '#connection-name' ).on( 'change keyup paste', function() {
			matchNamePattern( this, 'connection' );
		});

		// Mirror name pattern detection.
		$( '#mirror-name' ).on( 'change keyup paste', function() {
			matchNamePattern( this, 'mirror' );
		});

		/**
		 * Wizard Name Pattern Match.
		 *
		 * @param {element} wizardNameInput - Wizard Name Element.
		 * @param {string}  nameType        - Wizard Type.
		 */
		function matchNamePattern( wizardNameInput, nameType ) {
			var searchName = $( wizardNameInput ).val();
			var wizardNameError = $( wizardNameInput ).parent().find( 'span.error' );
			var nameLength = searchName.length;
			var namePattern = /^[a-z\d\_]+$/i; // Upper and lower case alphabets, numbers, and underscore allowed.

			// Hide wizard name error.
			wizardNameError.hide();

			// Configure connection/mirror view.
			if ( '' !== scriptData.connection || '' !== scriptData.mirror ) {
				configSaveBtn.removeAttr( 'disabled' );
			} else {
				// Wizard connection/mirror view.
				if ( 'connection' === nameType ) {
					wizardSave.removeAttr( 'disabled' ); // Remove wizard save btn disabled attribute.
				} else if ( 'mirror' === nameType ) {
					wizardNext.removeAttr( 'disabled' ); // Remove wizard next btn disabled attribute.
				}
			}


			if ( ( nameLength && ! namePattern.test( searchName ) ) || 25 < nameLength ) {
				wizardNameError.show();

				// Configure connection/mirror view.
				if ( '' !== scriptData.connection || '' !== scriptData.mirror ) {
					configSaveBtn.attr( 'disabled', 'disabled' );
				} else {
					// Wizard connection/mirror view.
					if ( 'connection' === nameType ) {
						wizardSave.attr( 'disabled', 'disabled' );
					} else if ( 'mirror' === nameType ) {
						wizardNext.attr( 'disabled', 'disabled' );
					}
				}
			}
		}

		// Papertrail location pattern detection.
		$( '#papertrail-dest' ).on( 'change keyup paste', function() {
			matchPapertrailLocationPattern( this, 'papertrail' );
		});

		/**
		 * Papertrail App Location Pattern Match.
		 *
		 * @param {element} inputStr  - Input.
		 * @param {string}  inputType - Input Type.
		 */
		function matchPapertrailLocationPattern( inputStr, inputType ) {
			var searchName = $( inputStr ).val();
			var inputError = $( inputStr ).parent().find( 'span.error' );
			var strPattern = /^[a-z\d]+(.papertrailapp.com:)[\d]+$/i;

			// Hide wizard name error.
			inputError.hide();

			if ( '' !== scriptData.connection || '' !== scriptData.mirror ) {
				configSaveBtn.removeAttr( 'disabled' );
			} else if ( 'papertrail' === inputType ) {
				wizardNext.removeAttr( 'disabled' ); // Remove wizard next btn disabled attribute.
			}

			if ( ! strPattern.test( searchName ) ) {
				inputError.show();

				if ( '' !== scriptData.connection || '' !== scriptData.mirror ) {
					configSaveBtn.attr( 'disabled', 'disabled' );
				} else if ( 'papertrail' === inputType ) {
					wizardNext.attr( 'disabled', 'disabled' );
				}
			}
		}

		// IP address pattern detection.
		$( '#syslog-remote-ip' ).on( 'change keyup paste', function() {
			matchIPAddressPattern( this, 'syslog' );
		});

		/**
		 * IP Address Pattern Match.
		 *
		 * @param {element} inputStr  - Input.
		 * @param {string}  inputType - Input Type.
		 */
		function matchIPAddressPattern( inputStr, inputType ) {
			var searchName = $( inputStr ).val();
			var inputError = $( inputStr ).parent().find( 'span.error' );
			var strPattern = /^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$|^(([a-zA-Z]|[a-zA-Z][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z]|[A-Za-z][A-Za-z0-9\-]*[A-Za-z0-9])$|^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*$/;

			// Hide wizard name error.
			inputError.hide();

			if ( '' !== scriptData.connection || '' !== scriptData.mirror ) {
				configSaveBtn.removeAttr( 'disabled' ); // Remove configure save btn disabled attribute.
			} else if ( 'syslog' === inputType ) {
				wizardNext.removeAttr( 'disabled' ); // Remove wizard next btn disabled attribute.
			}

			if ( ! strPattern.test( searchName ) ) {
				inputError.show();

				if ( '' !== scriptData.connection || '' !== scriptData.mirror ) {
					configSaveBtn.attr( 'disabled', 'disabled' );
				} else if ( 'syslog' === inputType ) {
					wizardNext.attr( 'disabled', 'disabled' );
				}
			}
		}

		// Mirror name pattern detection.
		$( '#syslog-remote-port' ).on( 'change keyup paste', function() {
			matchPortPattern( this, 'syslog' );
		});

		/**
		 * Port Pattern Match.
		 *
		 * @param {element} inputStr  - Input.
		 * @param {string}  inputType - Input Type.
		 */
		function matchPortPattern( inputStr, inputType ) {
			var searchName = $( inputStr ).val();
			var inputError = $( inputStr ).parent().find( 'span.error' );
			var strPattern = /^([1-9]|[1-8][0-9]|9[0-9]|[1-8][0-9]{2}|9[0-8][0-9]|99[0-9]|[1-8][0-9]{3}|9[0-8][0-9]{2}|99[0-8][0-9]|999[0-9]|[1-5][0-9]{4}|6[0-4][0-9]{3}|65[0-4][0-9]{2}|655[0-2][0-9]|6553[0-5])$/;

			// Hide wizard name error.
			inputError.hide();

			if ( '' !== scriptData.connection || '' !== scriptData.mirror ) {
				configSaveBtn.removeAttr( 'disabled' ); // Remove configure save btn disabled attribute.
			} else if ( 'syslog' === inputType ) {
				wizardNext.removeAttr( 'disabled' ); // Remove wizard next btn disabled attribute.
			}

			if ( ! strPattern.test( searchName ) ) {
				inputError.show();

				if ( '' !== scriptData.connection || '' !== scriptData.mirror ) {
					configSaveBtn.attr( 'disabled', 'disabled' );
				} else if ( 'syslog' === inputType ) {
					wizardNext.attr( 'disabled', 'disabled' );
				}
			}
		}

		// Slack bot name pattern detection.
		$( '#slack-bot' ).on( 'change keyup paste', function() {
			matchSlackBotNamePattern( this, 'slack' );
		});

		/**
		 * Slack Bot Name Pattern Match.
		 *
		 * @param {element} inputStr  - Input.
		 * @param {string}  inputType - Input Type.
		 */
		function matchSlackBotNamePattern( inputStr, inputType ) {
			var searchName = $( inputStr ).val();
			var inputError = $( inputStr ).parent().find( 'span.error' );
			var inputLength = searchName.length;
			var namePattern = /^[a-z\d\.\-\_]+$/i;

			// Hide error.
			inputError.hide();

			if ( '' !== scriptData.connection || '' !== scriptData.mirror ) {
				configSaveBtn.removeAttr( 'disabled' ); // Remove configure save btn disabled attribute.
			} else if ( 'slack' === inputType ) {
				wizardNext.removeAttr( 'disabled' ); // Remove wizard next btn disabled attribute.
			}

			if ( ( inputLength && ! namePattern.test( searchName ) ) || 21 < inputLength ) {
				inputError.show();

				if ( '' !== scriptData.connection || '' !== scriptData.mirror ) {
					configSaveBtn.attr( 'disabled', 'disabled' );
				} else if ( 'slack' === inputType ) {
					wizardNext.attr( 'disabled', 'disabled' );
				}
			}
		}

		// Slack webhook URL pattern detection.
		$( '#slack-webhook-url' ).on( 'change keyup paste', function() {
			matchSlackWebhookPattern( this, 'slack' );
		});

		/**
		 * Slack Webhook URL Pattern Match.
		 *
		 * @param {element} inputStr  - Input.
		 * @param {string}  inputType - Input Type.
		 */
		function matchSlackWebhookPattern( inputStr, inputType ) {
			var searchName = $( inputStr ).val();
			var inputError = $( inputStr ).parent().find( 'span.error' );
			var strPattern = /https:\/\/hooks.slack.com\/services\//;

			// Hide error.
			inputError.hide();

			if ( '' !== scriptData.connection || '' !== scriptData.mirror ) {
				configSaveBtn.removeAttr( 'disabled' ); // Remove configure save btn disabled attribute.
			} else if ( 'slack' === inputType ) {
				wizardNext.removeAttr( 'disabled' ); // Remove wizard next btn disabled attribute.
			}

			if ( ! strPattern.test( searchName ) ) {
				inputError.show();

				if ( '' !== scriptData.connection || '' !== scriptData.mirror ) {
					configSaveBtn.attr( 'disabled', 'disabled' );
				} else if ( 'slack' === inputType ) {
					wizardNext.attr( 'disabled', 'disabled' );
				}
			}
		}

		/**
		 * Run Mirror Manually.
		 */
		jQuery( '.wsal-mirror-run-now' ).click( function( event ) {
			var mirrorRunBtn = jQuery( this );
			mirrorRunBtn.html( scriptData.mirrorInProgress );
			mirrorRunBtn.attr( 'disabled', true );
			event.preventDefault();

			// Ajax request to run mirror manually.
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				async: true,
				dataType: 'json',
				data: {
					action: 'wsal_run_mirror',
					wpnonce: mirrorRunBtn.data( 'nonce' ),
					mirror: mirrorRunBtn.data( 'mirror' )
				},
				success: function( data ) {
					if ( data.success ) {
						mirrorRunBtn.html( scriptData.mirrorComplete );
					} else {
						mirrorRunBtn.html( scriptData.mirrorFailed );
						console.log( data.message );
					}
				},
				error: function( xhr, textStatus, error ) {
					mirrorRunBtn.html( scriptData.mirrorFailed );
					console.log( xhr.statusText );
					console.log( textStatus );
					console.log( error );
				}
			});
		});
	});

}( jQuery ) );
