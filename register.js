document.getElementById("registerForm")
.addEventListener("submit",function(e){

let pass=document.querySelector("[name=password]").value;
let confirm=document.querySelector("[name=confirm]").value;

if(pass!==confirm){
alert("Passwords do not match");
e.preventDefault();
}
});

let timeLeft = 30;
let timer = document.getElementById("timer");

let countdown = setInterval(function(){
    timer.innerHTML = "OTP expires in " + timeLeft + " sec";

    timeLeft--;

    if(timeLeft < 0){
        clearInterval(countdown);
        timer.innerHTML = "OTP expired. Click resend.";
    }
},1000);

