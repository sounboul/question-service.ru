vcl 4.0;

# Default backend definition. Set this to point to your content server.
# @see https://varnish-cache.org/docs/6.5/index.html
backend default {
    .host = "nginx";
    .port = "80";
}

# Happens before we check if we have this in cache already.
# Typically you clean up the request here, removing cookies you don't need, rewriting the request, etc.
sub vcl_recv {
    # Статические файлы будут обработа отдельно
    if (req.url ~ "/assets") {
        return (hash);
    }

    # Для корректной генерации URL
    if (req.http.X-Forwarded-Proto == "https" ) {
        set req.http.X-Forwarded-Port = "443";
    } else {
        set req.http.X-Forwarded-Port = "80";
    }

    // Удалить все cookie, кроме ID сессии.
    if (req.http.Cookie) {
        set req.http.Cookie = ";" + req.http.Cookie;
        set req.http.Cookie = regsuball(req.http.Cookie, "; +", ";");
        set req.http.Cookie = regsuball(req.http.Cookie, ";(PHPSESSID)=", "; \1=");
        set req.http.Cookie = regsuball(req.http.Cookie, ";[^ ][^;]*", "");
        set req.http.Cookie = regsuball(req.http.Cookie, "^[; ]+|[; ]+$", "");

        if (req.http.Cookie == "") {
            // Если cookie больше нет, удалите заголовок для кеширования страницы.
            unset req.http.Cookie;
        }
    }

    // Добавить заголовок Surrogate-Capability, чтобы афишировать поддержку ESI.
    set req.http.Surrogate-Capability = "abc=ESI/1.0";
}

# Happens after we have read the response headers from the backend. # Here you clean the response headers, removing silly Set-Cookie
# headers and other mistakes your backend does.
sub vcl_backend_response {
    # Если это статически е файлы, то указываем большой срок кэша и отдаем
    if (bereq.url ~ "/assets") {
        unset beresp.http.set-cookie;
        set beresp.http.cache-control = "public, max-age=31536000";
        set beresp.ttl = 365d;
        return (deliver);
    }

    # Все страницы без кук автоматически кэшируются на минуту
    if (beresp.status == 200) {
        unset beresp.http.Cache-Control;
        set beresp.http.Cache-Control = "public; max-age=60";
        set beresp.ttl = 60s;
    }

    set beresp.http.Served-By = beresp.backend.name;
    set beresp.http.V-Cache-TTL = beresp.ttl;
    set beresp.http.V-Cache-Grace = beresp.grace;

    // Проверить подтверждение ESI и удалить заголовок Surrogate-Control
    if (beresp.http.Surrogate-Control ~ "ESI/1.0") {
        unset beresp.http.Surrogate-Control;
        set beresp.do_esi = true;
    }
}

# Happens when we have all the pieces we need, and are about to send
# the response to the client. You can do accounting or modifying the # final object here.
sub vcl_deliver {
    # Информация о кэше в ответе пользователю
    if (obj.hits > 0) {
        set resp.http.V-Cache = "HIT";
    } else {
        set resp.http.V-Cache = "MISS";
    }
}
