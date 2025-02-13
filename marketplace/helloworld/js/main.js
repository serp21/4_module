"use strict";


// ==== Бот ====

const saveBotBtn = document.querySelector("[data-type=saveBot]");
const nameBotAvatarLabel = document.querySelector("[data-type=nameBotAvatar]");
const botAvatarInput = document.querySelector("[data-type=outBotAvatar]");

async function saveBot(data) {

    const preloader = document.querySelector(".preloader");
    preloader.classList.add("active");

    let response = await fetch("./functions/regBot.php", {
        method: "POST",
        cache: "no-cache",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(data)
    });

    
    if(response.ok) {
        let responseJSON = await response.json();
        document.location.reload();
    }
}
if(botAvatarInput) {
    botAvatarInput.addEventListener("change", (event) => {

        let imgName = event.target.files[0].name;
    
        nameBotAvatarLabel.childNodes[0].data = imgName;
    });
}


if(saveBotBtn) {
    saveBotBtn.addEventListener("click", (event) => {
        event.preventDefault();

        const botName = document.querySelector("[data-type=outBotName]");
        const botAvatar = document.querySelector("[data-type=outBotAvatar]");
        let botData = {};

        if(botName.value == "") {
            botName.classList.add('_error');
            return;
        }


        var fileBlob = "";

        
    
        if(botAvatar.files.length > 0) {
    
            let file = botAvatar.files[0];
            let reader = new FileReader();

            let file2b64 = reader.readAsDataURL(file);
            reader.onload = () => {
                fileBlob = reader.result;
                botData.NAME = botName.value;
                botData.AVATAR = fileBlob;
                saveBot(botData);
            };
            
        } else {
            botData.NAME = botName.value;
            saveBot(botData);
        }
        
    });
}


// ========================

const saveTextMessage = document.querySelector('[data-type="outMessage"]');
const sendMessageToMeBtn = document.querySelector('[data-type="sendMessage"]');

async function saveMessage(message) {
    if(message == "") {
        return false;
    }

    let messageOut = {
        MESSAGE: message
    }

    const loaded = document.querySelector(".preloader__from-btn_loaded");
    const success = document.querySelector(".preloader__from-btn_success");

    success.classList.remove("active");
    loaded.classList.add("active");

    const response = await fetch("./functions/saveMessage.php", {
        method: "POST",
        cache: "no-cache",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(messageOut)
      });
    
      if(response.ok) {
        let responseJSON = await response.json();
        loaded.classList.remove("active");
        if(!responseJSON.error) {
            success.classList.add("active");
            setTimeout(() => {
                success.classList.remove("active");
            }, 2000);
            return true;
        } else {
            console.log(responseJSON.error);
            return false;
        }
        
      }
      
}

async function saveActiveBot(idBot) {
    if(idBot == "") {
        return false;
    }

    let idBotOut = {
        "ACTIVE_BOT_ID": idBot
    }

    const response = await fetch("./functions/saveActiveBot.php", {
        method: "POST",
        cache: "no-cache",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(idBotOut)
      });
    
      if(response.ok) {
        let responseJSON = await response.json();
        
        if(!responseJSON.error) {
            
            return true;
        } else {
            console.log(responseJSON.error);
            return false;
        }
        
      }
}

async function sendMessageToMe(idUser) {

    let idUserOut = {"USER_ID": idUser}

    const loaded = document.querySelector(".preloader__from-btn_loaded");
    const success = document.querySelector(".preloader__from-btn_success");

    success.classList.remove("active");
    loaded.classList.add("active");

    const response = await fetch("./functions/sendMessageToMe.php", {
        method: "POST",
        cache: "no-cache",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(idUserOut)
      });
    
      if(response.ok) {
        let responseJSON = await response.json();
        loaded.classList.remove("active");
        
        if(!responseJSON.error) {
            console.log("Зашли");
            success.classList.add("active");
            setTimeout(() => {
                success.classList.remove("active");
            }, 2000);
            return true;
        } else {
            console.log(responseJSON.error);
            return false;
        }
        
      }
}

sendMessageToMeBtn.addEventListener("click", (event) => {
    event.preventDefault();

    let sendMessageToMeBtn = document.querySelector('[data-type=sendMessage]');
    let idUser = sendMessageToMeBtn.getAttribute("data-id");
    sendMessageToMe(idUser);
});

saveTextMessage.addEventListener("blur", (event) => {
    event.preventDefault();

    const outMessageElem = document.querySelector('[data-type="outMessage"]');
    let outMessageValue = outMessageElem.value;

    if(outMessageValue == "") {
        return;
    }

    saveMessage(outMessageValue);

    
});


