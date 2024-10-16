const addLocation = document.querySelector("#addLocation")
const viewLocation = document.querySelector("#viewLocation")
const actionPublier = document.querySelector("#flexSwitchCheckChecked")
addLocation.addEventListener("click", (event) => {
    if (viewLocation.classList.contains('visually-hidden')) {
        viewLocation.classList.remove('visually-hidden')
    } else {
        viewLocation.classList.add('visually-hidden')
    }
})

actionPublier.addEventListener('change', (event) => {
    console.log(actionPublier.checked)
})