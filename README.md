# eBay_autopay
How to use 
1. Download mkato-eBay.php
2. Install Xampp (Desktop) / PHP (Vps)
3. Edit your address shipping & input cc in a file like cc.txt


Card Format :
ccnum|month|year|cvv|firstname|lastname|addr1|addr2|city|state|phone|

example 1 without addr2: 

5131237843123458|11|19|992|OMOM|MANTAB|2 rue des petites||Lyon|Rhones|69003|

example 2 with addr2   : 

4508275421268901|09|22|927|Bonn|Appetit|Santiago Madrigal|esc. 7 1B|Salamanca|España|37003|

Use Telegram Notification:
1. Input your user id (chat_id) in script
2. Add telegram @mkato_autopaybot 
3. type /start  in to start the bot
4. set true notiftotelegram

Get Telegram User_ID :
1. Add @get_id_bot
2. type /start
3. Copy your user id



How to find item link:
1. Open eBay.com
2. Choose your item
3. Open Network Tab by hit ctrl+shift+i on firefox / F12 on chrome
4. Click Buy Now
5. Click Guest Checkout
6. Find rxgo? in network tab
7. Copy Request_URL, this is the link
