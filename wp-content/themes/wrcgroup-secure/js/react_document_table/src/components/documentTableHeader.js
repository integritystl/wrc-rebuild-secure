import React from 'react';

class DocumentTableHeader extends React.Component {
  render(){
    let hiddenStatesClass = ""
    if(this.props.hideStates === '1'){
      hiddenStatesClass = ' hidden_states';
    }
    return(
      <div className={"document_table_header " + this.props.sort + " " + this.props.sortDirection + hiddenStatesClass}>
        <span onClick={this.props.nameSort} className="document_table_header_title">Name <i className="fa fa-chevron-down"></i><i className="fa fa-chevron-up"></i></span>
        <span className="document_table_header_filetype">Type</span>
        {this.props.hideStates !== '1' && 
        <span className="document_table_header_state">State</span>
        }
        <span onClick={this.props.dateSort} className="document_table_header_date">Date Modified <i className="fa fa-chevron-down"></i><i className="fa fa-chevron-up"></i></span>
      </div>
    );
  }
}

module.exports = DocumentTableHeader;
