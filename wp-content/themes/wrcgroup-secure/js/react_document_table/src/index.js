import React from 'react';
import ReactDOM from 'react-dom';

//Libs
import Moment from 'moment';

//Components
import DocumentTableHeader from './components/documentTableHeader.js';
import DocumentTable from './components/documentTable.js';

class App extends React.Component {

  componentWillMount() {
    var sortDirection = 'desc';
    if(tableParams.reverse_sort && tableParams.reverse_sort == 1){
      sortDirection = 'asc';
    }
    this.setState({
      'documents': tableParams.documents,
      'allDocuments': tableParams.documents,
      'userStates': tableParams.user_states,
      'hide_states' : tableParams.hide_states,
      'selectedState': 'ALL',
      'sort': 'name',
      'sortDirection': sortDirection,
      'searchTerm': '',
    }, () => this.sortDocumentsByName());

  }
  sortDocumentsByName() {
    let sortDirection = 'asc';
    if(this.state.sort === 'name' && this.state.sortDirection === 'asc'){
      sortDirection = 'desc';
    }
    let sortableDocuments = this.state.documents.slice();
    sortableDocuments.sort(function(a, b){
      if(a.title < b.title) return sortDirection === 'asc'? -1 : 1;
      else return sortDirection === 'asc'? 1 : -1;
    });
    this.setState({
      'documents': sortableDocuments,
      'sort': 'name',
      'sortDirection': sortDirection
    })
  }

  sortDocumentsByDate() {
    let sortDirection = 'asc';
    if(this.state.sort === 'date' && this.state.sortDirection === 'asc'){
      sortDirection = 'desc';
    }
    let sortableDocuments = this.state.documents.slice();
    sortableDocuments.sort(function(a, b){
      let aFiles = a.files
      aFiles.sort(function(c,d){
        let cDate = Moment(c.modified, 'MMM DD, YYYY');
        let dDate = Moment(d.modified, 'MMM DD, YYYY');
        if(cDate.format('YYYYMMDD') < dDate.format('YYYYMMDD')) return sortDirection === 'asc'? -1 : 1;
        else return sortDirection === 'asc'? 1 : -1;
      })
      a.files = aFiles

      let bFiles = b.files
      bFiles.sort(function(c,d){
        let cDate = Moment(c.modified, 'MMM DD, YYYY');
        let dDate = Moment(d.modified, 'MMM DD, YYYY');
        if(cDate.format('YYYYMMDD') < dDate.format('YYYYMMDD')) return sortDirection === 'asc'? -1 : 1;
        else return sortDirection === 'asc'? 1 : -1;
      })
      b.files = bFiles

      let aDate = Moment(a.files[0].modified, 'MMM DD, YYYY');
      //console.log(aDate)
      let bDate = Moment(b.files[0].modified, 'MMM DD, YYYY');
      //console.log(bDate)
      if(aDate.format('YYYYMMDD') < bDate.format('YYYYMMDD')){
        return sortDirection === 'asc'? -1 : 1;
      }
      else if(aDate.format('YYYYMMDD') == bDate.format('YYYYMMDD')){
        if(sortDirection === 'asc'){
          if(a.title < b.title){
            return -1
          }
          return 1
        }else{
          if(a.title < b.title){
            return 1
          }
          return -1
        }
      }
      else{
        return sortDirection === 'asc'? 1 : -1;
      }
    });
    this.setState({
      'documents': sortableDocuments,
      'sort': 'date',
      'sortDirection': sortDirection
    });
  }
  updateSearchTerm(evt) {
    this.setState({
      searchTerm: evt.target.value
    }, () => { this.filterDocumentsBySearchTerm() });
  }
  updateSelectedState(evt){
    this.setState({
      selectedState: evt.target.value
    }, () => { this.filterDocumentsByState() });
  }
  //Sorts by the searchTerm. We use allDocuments as that state value is never altered.
  //This allows us to "get back" items that have already been filtered out
  filterDocumentsBySearchTerm() {
    let sortableDocuments = this.state.allDocuments.slice();

    let returnDocuments = sortableDocuments.filter((document) => {
      return (document.title.toLowerCase().indexOf(this.state.searchTerm.toLowerCase()) > -1 || document.description.toLowerCase().indexOf(this.state.searchTerm.toLowerCase()) > -1 );
    });

    this.setState({
      'documents': returnDocuments
    });
  }
  filterDocumentsByState(){

    let sortableDocuments = this.state.allDocuments.slice(0);

    let returnDocuments = sortableDocuments.filter((document) => {
      return (this.state.selectedState == "ALL" || document.states.indexOf(this.state.selectedState) > -1)
    });

    this.setState({
      'documents': returnDocuments
    });
  }

  resetFilter(){
    //reset to our settings from when we loaded the component
    var sortDirection = 'desc';
    if(tableParams.reverse_sort && tableParams.reverse_sort == 1){
      sortDirection = 'asc';
    }
    this.setState({
      'documents': tableParams.documents,
      'allDocuments': tableParams.documents,
      'userStates': tableParams.user_states,
      'hide_states': tableParams.hide_states,
      'selectedState': 'ALL',
      'sort': 'name',
      'sortDirection': sortDirection,
      'searchTerm': ''
    }, () => { this.sortDocumentsByName() });
  }
  render() {
    let stateOptions = null;
    stateOptions = this.state.userStates.map((userState) => {
      return <option key={userState} value={userState}>{userState}</option>
    })

    let filterCloseClass = "fa fa-close";

    if (this.state.searchTerm) {
      filterCloseClass += " visible";
    }

    return (
      <div className="document_table_wrapper">
        <div className="document_table_filter_wrapper">
          {this.state.userStates.length > 1 && this.state.hide_states === false &&
            <div className="document_table_state_dropdown">
              <span className="document_table_state_dropdown_label">Filter by State</span>
              <i className="fa fa-chevron-down"></i>
              <select value={this.state.selectedState} onChange={evt => this.updateSelectedState(evt)}>
                <option key="ALL" value="ALL">All</option>
                {stateOptions}
              </select>
            </div>
          }
          <div className="document_table_searchbox">
            <i className="fa fa-filter"></i>
            <input className="document_table_searchbox_input" type="text" value={this.state.searchTerm} onChange={evt => this.updateSearchTerm(evt)} placeholder="Filter Docs"/>
            <i className={filterCloseClass} onClick={() => this.resetFilter() }></i>
          </div>
        </div>
        <DocumentTableHeader
          nameSort={() => this.sortDocumentsByName()}
          dateSort={() => this.sortDocumentsByDate()}
          sort={this.state.sort}
          sortDirection={this.state.sortDirection}
          hideStates={this.state.hide_states}
        />
        <DocumentTable documents={this.state.documents} filterStates={this.state.userStates} hideStates={this.state.hide_states}/>
      </div>
    );
  }
}

let rootElem = document.getElementById('document_table');
if(rootElem) {
  ReactDOM.render(<App />, rootElem);
}
