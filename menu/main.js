const tables = document.getElementsByClassName("table");
const monday = () => {
    for (let table of tables) {
        table.rows[2].style.display = "block"
        for (let i = 3; i < 7; i++) {
            table.rows[i].style.display = "none"
        }
    }
}
const tuesday = () => {
    for (let table of tables) {
        table.rows[2].style.display = "none"
        table.rows[3].style.display = "block"
        for (let i = 4; i < 7; i++) {
            table.rows[i].style.display = "none"
        }
    }
}
const wednesday = () => {
    for (let table of tables) {
        for (let i = 2; i < 4; i++) {
            table.rows[i].style.display = "none"
        }
        table.rows[4].style.display = "block"
        for (let i = 5; i < 7; i++) {
            table.rows[i].style.display = "none"
        }
    }
}
const thursday = () => {
    for (let table of tables) {
        for (let i = 2; i < 5; i++) {
            table.rows[i].style.display = "none"
        }
        table.rows[5].style.display = "block"
        table.rows[6].style.display = "none"
    }
}
const friday = () => {
    for (let table of tables) {
        for (let i = 2; i < 6; i++) {
            table.rows[i].style.display = "none"
        }
        table.rows[6].style.display = "block"
    }
}
const allWeek = () => {
    for (let table of tables) {
        for (let i = 2; i < 7; i++) {
            table.rows[i].style.display = "block"
        }
    }
}

