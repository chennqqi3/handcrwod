<?php if (!$this->script_mode()) { ?>
<header class="jumbotron subhead" id="overview">
    <div class="container">
        <h2>接続状況</h2><span id="status">未接続</span>
    </div>
</header>

<div class="container">
    <div class="row">
        <div class="span12">
            <section id="main">
                <div class="row">
                    <div class="span6">
                        <div id="clientList">
                            <h3>接続中のクライアント:</h3>
                            <select id="clientListSelect" multiple="multiple" style="height:200px"></select>
                        </div>
                    </div>
                    <div class="span6">
                        <div id="serverInfo">
                            <h3>ソケットサーバー情報:</h3>
                            <p>クライアント数: <span id="clientCount"></span></p>
                            <p>接続可能な最大クライアント数: <span id="maxClients"></span></p>
                            <p>最大コネクション数/IP: <span id="maxConnections"></span></p>
                            <p>最大リクエスト数/分: <span id="maxRequetsPerMinute"></span></p>
                        </div>
                    </div>
                </div>

                <hr>
                
                <div id="console">
                    <h3>サーバーメッセージ:</h3>
                    <div id="log"></div>
                </div>
            </section>
        </div>
    </div>
</div> <!-- /container -->
<?php } else { ?>
<script type="text/javascript">
$(document).ready(function() {
    log = function(msg) {
        return $('#log').prepend(msg + "<br />");
    };
    serverUrl = '<?php p(_chat_uri('status')); ?>';
    if (window.MozWebSocket) {
        socket = new MozWebSocket(serverUrl);
    } else if (window.WebSocket) {
        socket = new WebSocket(serverUrl);
    }
    socket.onopen = function(msg) {
        return $('#status').removeClass().addClass('label label-success').html('接続済み');
    };
    socket.onmessage = function(msg) {
        var response;
        response = JSON.parse(msg.data);
        switch (response.event) {
            case "statusMsg":
                return statusMsg(response.data);
            case "clientConnected":
                return clientConnected(response.data);
            case "clientDisconnected":
                return clientDisconnected(response.data);
            case "clientActivity":
                return clientActivity(response.data);
            case "serverInfo":
                return refreshServerinfo(response.data);
        }
    };
    socket.onclose = function(msg) {
        return $('#status').removeClass().addClass('label').html('未接続');
    };
    $('#status').click(function() {
        return socket.close();
    });
    statusMsg = function(msgData) {
        switch (msgData.type) {
            case "info":
                return log(msgData.text);
            case "warning":
                return log("<span class=\"warning\">" + msgData.text + "</span>");
        }
    };
    clientConnected = function(data) {
        $('#clientListSelect').append(new Option(data.ip + ":" + data.port, data.port));
        return $('#clientCount').text(data.clientCount);
    };
    clientDisconnected = function(data) {
        $("#clientListSelect option[value='" + data.port + "']").remove();
        return $('#clientCount').text(data.clientCount);
    };
    refreshServerinfo = function(serverinfo) {
        var ip, port, ref, results;
        $('#clientCount').text(serverinfo.clientCount);
        $('#maxClients').text(serverinfo.maxClients);
        $('#maxConnections').text(serverinfo.maxConnectionsPerIp);
        $('#maxRequetsPerMinute').text(serverinfo.maxRequetsPerMinute);
        ref = serverinfo.clients;
        results = [];
        for (port in ref) {
            ip = ref[port];
            results.push($('#clientListSelect').append(new Option(ip + ':' + port, port)));
        }
        return results;
    };
    return clientActivity = function(port) {
        return $("#clientListSelect option[value='" + port + "']").css("color", "red").animate({
            opacity: 100
        }, 600, function() {
            return $(this).css("color", "black");
        });
    };
});
</script>
<?php } ?>