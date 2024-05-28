// Assuming you have a JavaScript controller file
// Import fetch function if not already available in your environment
var ref = "";
var urlParams;
(window.onpopstate = function () {
    var match,
        pl     = /\+/g,  // Regex for replacing addition symbol with a space
        search = /([^&=]+)=?([^&]*)/g,
        decode = function (s) { return decodeURIComponent(s.replace(pl, " ")); },
        query  = window.location.search.substring(1);
  
    urlParams = {};
    while (match = search.exec(query))
       urlParams[decode(match[1])] = decode(match[2]);
})();
if (urlParams['ref'] == null) {
    //ref = "";
  }else{
    document.getElementsByName('ref').value = urlParams['ref']
  ref = urlParams['ref'];
}
// Attach event listener to the form submit event
document.getElementById('regForm').addEventListener('submit', function(event) {
    // Prevent the default form submission behavior
    event.preventDefault();

    // Serialize form data into a JSON object
    const formData = new FormData(this);
    const serializedFormData = {};
    formData.forEach((value, key) => {
        serializedFormData[key] = value;
    });

    let submitbtn = document.getElementById("submitbtn")
    submitbtn.setAttribute('disabled','true');
    submitbtn.innerHTML='<i class="fa fa-spinner  fa-pulse" aria-hidden="true"></i> Submitting...' 
    $.post("/register",
    serializedFormData,
    function(data,status){
     
     setTimeout(() => {
     
      Swal.fire(
        {
            icon: data.status,
            title: data.status,
            text: data.message,
            //background: '#000'
          }
       
      )
     }, 2000);
      submitbtn.removeAttribute("disabled");
      if (data.status == "success") {
        submitbtn.innerHTML = "Redirecting";
        setTimeout(function(){
          location.replace("/dashboard");
        },3000);
        
      }else{
        document.getElementById("submit").innerHTML = "Register";
      }
    });

    });
