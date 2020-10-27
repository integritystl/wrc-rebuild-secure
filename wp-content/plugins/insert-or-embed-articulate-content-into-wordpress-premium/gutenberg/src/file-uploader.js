/**
 * Internal block libraries
 */

const { __ } = wp.i18n;

const {
	ButtonGroup,
	Button
} = wp.components;

const {
	Component,
	Fragment
} = wp.element;

class FileUploader extends Component {
	constructor() {
		super( ...arguments );

		this.state = {
			notice: true
		};
	}

	componentDidMount() {
		const uploader = new plupload.Uploader({
			runtimes: 'html5,flash,silverlight,html4',
			'browse_button': 'chunkFileUpload',
			container: document.getElementById( 'articulate_uploader' ),
			url: articulateOptions.uploadData,
			'chunk_size': articulateOptions.plupload.chunk_size,
			'max_retries': articulateOptions.plupload.max_retries,
			'dragdrop': true,
			'multi_selection': false,
			'file_data_name': 'file',
			filters: {
				'max_file_size': '0',
				'mime_types': [
					{
						title: 'Zip files',
						extensions: 'zip'
					},
					{
						title: 'MP4 files',
						extensions: 'mp4'
					}
				]
			},
			init: {
				PostInit: () => {
					document.getElementById( 'chunkFileUploadButton' ).onclick = function() {
						uploader.start();
						return;
					};
				},

				UploadProgress: ( up, file ) => {
					if( 100 === up.total.percent ) {
					document.getElementById( 'fileArea' ).innerHTML = `${ file.name } uploaded. Unzipping content.`;
					}
					else {
					document.getElementById( 'fileArea' ).innerHTML = `${ file.name } is uploading at ${ up.total.percent }%`;	
					}
				},

				FileUploaded: ( upldr, file, object ) => {

					const info = jQuery.parseJSON( object.response );

					console.log( 'info: ' + info );
					document.getElementById( 'fileArea' ).innerHTML = info.info;
					if ( 1 === info.OK ) {
						this.props.insertData( info );
						this.setState({ notice: false });
						setTimeout(
							() => {
								const notice = document.getElementById( 'trial-notice' );
								if ( null !== notice ) {
									notice.classList.add( 'hide-notice' );
								}
							},
							500
						);
					}
				},

				FilesAdded: ( up, files ) => {
					if ( 1 < uploader.files.length ) {
						uploader.removeFile( uploader.files[0]);
					}
					document.getElementById( 'fileArea' ).innerHTML = `${ uploader.files[0].name } (${ ( ( uploader.files[0].size / 1024 ) / 1024 ).toFixed( 1 ) } mb)`;
				},

				Error: ( up, err ) => {
					console.log( err );
				}
			}
		});

		uploader.init();
	}

	render() {
		return (
			( ( this.props.options.dir > this.props.options.count ) && ( 2 === Number( this.props.options.count ) ) && ( true === this.state.notice ) ) ?
				<blockquote id="trial-notice" class="notice-blockquote flow-text pink-text lineheighter">
					The trial version only supports three uploads. Please purchase the full version with unlimited uploads at <a href="https://www.elearningfreak.com/">www.elearningfreak.com</a> - Already purchased? Go to Articulate > License on your Wordpress menu to activate your license key.
				</blockquote>	:
				<Fragment>
					<ButtonGroup id="articulate_uploader">
						<Button
							className="material-btn grey"
							id="chunkFileUpload"
						>
							{ __( 'Choose your zip file' ) }
						</Button>

						<Button
							id="chunkFileUploadButton"
							className="material-btn"
							isBusy={ this.isUploading ? true : false }
						>
							<i class="upload-icon">call_made</i>{ __( 'Upload!' ) }
						</Button>
					</ButtonGroup>
					<p class="fileArea" id="fileArea"></p>

				</Fragment>
		);
	}
}

export default FileUploader;
