const selectPlan = () => {
    let plan = document.getElementById("plan").value
    let planname = 'BASIC'
    let returnrate = 50
    let duration = 3
    let min = 5000
    switch (plan) {
        case '0':
             planname = 'BASIC'
             returnrate = 50
             duration = 3
             min = 5000

            document.getElementById('planname').textContent = planname
            document.getElementById('planduration').textContent =  `${duration} days`
            document.getElementById('planreturn').textContent = `${returnrate}% daily`
            document.getElementById('planmin').textContent = `$${min}`

            document.getElementById("pname").value =  planname
            document.getElementById("pduration").value =  duration
            document.getElementById("preturn").value = returnrate


            
            break;
        case '1':
             planname = 'STANDARD'
             returnrate = 28.50
             duration = 7
             min = 10000

            document.getElementById('planname').textContent = planname
            document.getElementById('planduration').textContent =  `${duration} days`
            document.getElementById('planreturn').textContent = `${returnrate}% daily`
            document.getElementById('planmin').textContent = `$${min}`

            document.getElementById("pname").value =  planname
            document.getElementById("pduration").value =  duration
            document.getElementById("preturn").value = returnrate
            break;
        case '2':
            planname = 'PREMIUM'
             returnrate = 21.42
             duration = 14
             min = 50000

            document.getElementById('planname').textContent = planname
            document.getElementById('planduration').textContent =  `${duration} days`
            document.getElementById('planreturn').textContent = `${returnrate}% daily`
            document.getElementById('planmin').textContent = `$${min}`

            document.getElementById("pname").value =  planname
            document.getElementById("pduration").value =  duration
            document.getElementById("preturn").value = returnrate
            break;
        case '3':
            planname = 'HUT 8'
             returnrate = 5
             duration = 7
             min = 1500

            document.getElementById('planname').textContent = planname
            document.getElementById('planduration').textContent =  `${duration} days`
            document.getElementById('planreturn').textContent = `${returnrate}% daily`
            document.getElementById('planmin').textContent = `$${min}`

            document.getElementById("pname").value =  planname
            document.getElementById("pduration").value =  duration
            document.getElementById("preturn").value = returnrate
            break;
        case '4':
            planname = 'CIPHER MINING'
             returnrate = 7
             duration = 7
             min = 100

            document.getElementById('planname').textContent = planname
            document.getElementById('planduration').textContent =  `${duration} days`
            document.getElementById('planreturn').textContent = `${returnrate}% daily`
            document.getElementById('planmin').textContent = `$${min}`

            document.getElementById("pname").value =  planname
            document.getElementById("pduration").value =  duration
            document.getElementById("preturn").value = returnrate
            break;
        default:
            planname = 'Riot Blockchain'
             returnrate = 1.75
             duration = 4
             min = 100

            document.getElementById('planname').textContent = planname
            document.getElementById('planduration').textContent =  `${duration} days`
            document.getElementById('planreturn').textContent = `${returnrate}% daily`
            document.getElementById('planmin').textContent = `$${min}`

            document.getElementById("pname").value =  planname
            document.getElementById("pduration").value =  duration
            document.getElementById("preturn").value = returnrate
            break;
    }
    document.getElementById('invbtn').removeAttribute('disabled')
}

const selectAmount = (e) => {
    document.getElementById("amm").value = e
    document.getElementById("investamount").textContent = `$${e}`
    document.getElementById("pamm").value = e
}

const setAmount = () => {
    let e =  document.getElementById("amm").value
    
    if(isNaN(e)) {
        e = 100
    }
    document.getElementById("investamount").textContent = `$${e}`
    document.getElementById("pamm").value = e
}