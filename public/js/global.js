const addLocation = document.querySelector("#addLocation")
const viewLocation = document.querySelector("#viewLocation")
addLocation.addEventListener("click", (event) => {
    if (viewLocation.hasAttribute('hidden')) {
        viewLocation.removeAttribute('hidden')
        document.querySelector("#location_locStreet").setAttribute('required', 'true')
        document.querySelector("#city_citPostCode").setAttribute('required', 'true')
        document.querySelector("#city_citName").setAttribute('required', 'true')
        document.querySelector("#location_locLatitude").setAttribute('required', 'true')
        document.querySelector("#location_locLongitude").setAttribute('required', 'true')
    } else {
        viewLocation.setAttribute('hidden', '')
        document.querySelector("#location_locStreet").removeAttribute("required")
        document.querySelector("#city_citPostCode").removeAttribute("required")
        document.querySelector("#city_citName").removeAttribute("required")
        document.querySelector("#location_locLatitude").removeAttribute("required")
        document.querySelector("#location_locLongitude").removeAttribute("required")
    }
})