import React from 'react';
import Document from './document.js';

class DocumentTable extends React.Component {
  render() {
    let documents = this.props.documents;
    let visibleStates = this.props.filterStates;
    let hideStates = this.props.hideStates;
    let documentComponents = null;
 
    documentComponents = this.props.documents.map((document) => {
      return <Document key={document.id} document={document} visibleStates={visibleStates} hideStates={hideStates}/>
    })
    return(
      <div className="document_table">
        {documentComponents}
      </div>
    );
  }
}

module.exports = DocumentTable;
