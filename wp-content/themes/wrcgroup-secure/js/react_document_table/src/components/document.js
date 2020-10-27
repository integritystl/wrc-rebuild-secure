import React from 'react';

class Document extends React.Component {
  render() {
    let documentReturn = null;
    let states = this.props.document.states.filter((state) => {
      return (this.props.visibleStates.indexOf(state) > -1)
    });
    states = states.join(", ");

    let hiddenStatesClass = ""
    if(this.props.hideStates === '1'){
      hiddenStatesClass = ' hidden_states';
    }
    
    documentReturn = this.props.document.files.map((file, index) => {
      let title = index == 0 ? this.props.document.title: '';
      let description = index == 0 ? this.props.document.description: '';
      
      let date = file.modified;
      let iconClass = '';
      let iconString = '';
      //Switch statement to set the font awesome icon for the filetype
      switch(file.mime) {
        case 'application/pdf':
          iconClass = 'fa fa-file-pdf-o';
          iconString = 'PDF'
          break;
        case 'application/msword':
          iconClass = 'fa fa-file-word-o';
          iconString = 'WORD';
          break;
        case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
          iconClass = 'fa fa-file-word-o';
          iconString = 'WORD';
          break;
        case 'application/vnd.ms-excel':
          iconClass = 'fa fa-file-excel-o';
          iconString = 'EXCEL';
          break;
        case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
          iconClass = 'fa fa-file-excel-o';
          iconString = 'EXCEL';
          break;
        default:
          iconClass = 'fa fa-file-text-o';
          iconString = 'FILE';
      }

      return(
        <a key={this.props.document.id + '-' + index} id={'document_' + this.props.document.id} className="document_download_link document_download_files" href={file.url} target="_blank" download>
          <div className="document_item_file">
            <span className="document_item_file_filetype"><i className={iconClass}></i> {iconString}</span>
            {this.props.hideStates !== '1' &&
            <span className="document_item_file_state">{states}</span>
            }
            <span className="document_item_file_date">{date}</span>
          </div>
        </a>
      );
    });
 
    let descriptionLink = this.props.document.files.length > 0 ? this.props.document.files[0].url : '';
    return(
      <div className={"document_table_item" + hiddenStatesClass}>
        <a id={'document_desc_' + this.props.document.id} className="document_download_link document_desc" href={descriptionLink} target="_blank" download>
          <div className="document_item_files_description">
            <span className="document_item_file_title">
              {this.props.document.title}
              <span className="document_item_file_description" dangerouslySetInnerHTML={{__html: this.props.document.description}}></span>
            </span>
          </div>
        </a>
        <div className="document_item_files_list">
          {documentReturn}
        </div>
      </div>
    )
  }
}

module.exports = Document;
