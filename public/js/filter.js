// function checkedChekbox(checkboxName){
//     const checkboxe = document.getElementById(checkboxName)
//         if(checkboxe.checked == false) return 'no';
//         return 'yes';
// }
//
// async function fetchSearchFilter(filters){
//     const response = await fetch('/search',{
//         method: "POST",
//         headers:{
//             "Content-Type" : "application/json"
//         },
//         body : JSON.stringify(filters)
//     })
//     return response.json();
// }
//  function setupEventFilter(){
//     const btnSearch = document.getElementById('search-trip');
//     btnSearch.addEventListener('click',async function () {
//         const site = document.getElementById('name-site').value;
//         const searchName = document.getElementById('search-name-site').value.toLowerCase();
//         const startDate = document.getElementById('start-date').value;
//         const endDate = document.getElementById('end-date').value
//         const organisatorTrip = checkedChekbox('organisator-trip')
//         const subcribeTrip = checkedChekbox('subcribed-trip');
//         const notSubcribeTrip = checkedChekbox('not-subscribed-trip');
//         const ancientTrip = checkedChekbox('ancient-trip');
//
//         const filters = {
//             'site': site,
//             'searchName': searchName,
//             'startDate': startDate,
//             'endDate': endDate,
//             'organisatorTrip': organisatorTrip,
//             'subcribeTrip': subcribeTrip,
//             'notSubcribeTrip': notSubcribeTrip,
//             'ancientTrip': ancientTrip
//         }
//         const filteredTtrips = await fetchSearchFilter(filters);
//         console.log(filteredTtrips );
//     })
// }
//
// function init(){
//     setupEventFilter();
// }
//
// init()