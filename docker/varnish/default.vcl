vcl 4.0;

# Default backend definition. Set this to point to your content server.
# @see https://varnish-cache.org/docs/6.5/index.html
backend default {
    .host = "nginx";
    .port = "80";
}

# Кому разрешено отправлять BAN запросы
acl purge {
    "127.0.0.1";
    "php-fpm";
}

# Happens before we check if we have this in cache already.
# Typically you clean up the request here, removing cookies you don't need, rewriting the request, etc.
sub vcl_recv {
    # Инвалидация кэша
    if (req.method == "BAN") {
        if (!client.ip ~ purge) {
            return (synth(405, "Not allowed."));
        }

        # по тегам
        if (req.http.X-Cache-Tags) {
            ban("obj.http.X-Cache-Tags ~ " + req.http.X-Cache-Tags);
        } else {
            return (synth(403, "X-Cache-Tags header missing."));
        }

        return (synth(200, "Ban added."));
    }

    # Статические файлы будут обработа отдельно
    if (req.url ~ "/assets") {
        return (hash);
    }

    # Исключение из кэша страниц админки, профиля, системы авторизации, поиска
    if (req.url ~ "^/backend/" || req.url ~ "^/profile/" || req.url ~ "^/user/" || req.url ~ "^/search/") {
        return (pass);
    }

    # Фрагменты будут включены игнорируя все куки
    if (req.url ~ "^/_fragment") {
        unset req.http.Cookie;
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
    # Если это статические файлы, то указываем большой срок кэша и отдаем
    if (bereq.url ~ "/assets") {
        unset beresp.http.set-cookie;
        set beresp.http.cache-control = "public, max-age=31536000";
        set beresp.ttl = 365d;
        return (deliver);
    }

    // Проверить подтверждение ESI и удалить заголовок Surrogate-Control
    if (beresp.http.Surrogate-Control ~ "ESI/1.0") {
        unset beresp.http.Surrogate-Control;
        set beresp.do_esi = true;
    }

    # Set ban-lurker friendly custom headers.
    set beresp.http.X-Url = bereq.url;
    set beresp.http.X-Host = bereq.http.host;
}

# Happens when we have all the pieces we need, and are about to send
# the response to the client. You can do accounting or modifying the # final object here.
sub vcl_deliver {
    # Информация о кэше в ответе пользователю
    if (obj.hits > 0) {
        set resp.http.X-Varnish-Cache = "HIT";
    } else {
        set resp.http.X-Varnish-Cache = "MISS";
    }

    # Remove ban-lurker friendly custom headers when delivering to client.
    unset resp.http.X-Url;
    unset resp.http.X-Host;
    unset resp.http.X-Cache-Tags;

    # Запрещаем кеширование на клиенте для динамических ресурсов.
    if (resp.http.Content-Type ~ "text/html") {
      set resp.http.Cache-Control = "private, no-cache";
    }
}
