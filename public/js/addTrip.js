const addLocation = document.querySelector("#addLocation")
const viewLocation = document.querySelector("#viewLocation")
const locationCity = document.querySelector('#locationCity')
const locationTrip = document.querySelector('#locationTrip')
const location_locStreet = document.querySelector("#location_locStreet")
const city_citPostCode = document.querySelector("#city_citPostCode")
const city_citName = document.querySelector("#city_citName")
const location_locLatitude = document.querySelector("#location_locLatitude")
const location_locLongitude = document.querySelector("#location_locLongitude")
const location_locCity = document.querySelector("#location_locCity")
let trip_triLocation = document.querySelector("#trip_triLocation")

addLocation.addEventListener("click", (event) => {
    if (viewLocation.hasAttribute('hidden')) {
        locationCity.setAttribute('hidden', '')
        locationTrip.setAttribute('hidden', '')

        viewLocation.removeAttribute('hidden')
        location_locStreet.setAttribute('required', 'true')
        city_citPostCode.setAttribute('required', 'true')
        city_citName.setAttribute('required', 'true')
        location_locLatitude.setAttribute('required', 'true')
        location_locLongitude.setAttribute('required', 'true')
    } else {
        locationCity.removeAttribute('hidden')
        locationTrip.removeAttribute('hidden')

        viewLocation.setAttribute('hidden', '')
        location_locStreet.removeAttribute("required")
        city_citPostCode.removeAttribute("required")
        city_citName.removeAttribute("required")
        location_locLatitude.removeAttribute("required")
        location_locLongitude.removeAttribute("required")
    }
})

location_locCity.addEventListener('change', (event) => {
    fetch('/trip/search-location/' + location_locCity.value)
        .then((response => response.json()))
        .then((data) => {
            let jsonData = JSON.parse(data)

            //Vider le select
            trip_triLocation.length = 0

            jsonData.results.forEach((element) => {
                let option = document.createElement('option')
                option.value = element.id
                option.text = element.name
                trip_triLocation.appendChild(option)
            })

        })
        .catch((error) => {
            console.log("Une erreur est survenue : ", error)
        })
})