<?php

Route::get('(:bundle)/(:any?)/(:any?)', function($section=false, $page=false) {
            $get_markup = function($doc_file) {
                        $full_doc_path = Bundle::path('docs') . "source/$doc_file.md";
                        $cache_enabled = Config::get('docs::options.cache_enabled');
                        
                        $update_cache = false;
                        if($cache_enabled){
                            $cache_key = Config::get('docs::options.cache_prefix') . preg_replace('#\W#', "_", $doc_file);
                            $parsed_data = Cache::get($cache_key);
                            clearstatcache();
                            $update_cache = is_null($parsed_data)
                                    || !is_array($parsed_data)
                                    || !array_key_exists('mtime', $parsed_data)
                                    || !array_key_exists('content', $parsed_data)
                                    || !is_int($parsed_data['mtime'])
                                    || (filemtime($full_doc_path) != $parsed_data['mtime']);
                        }

                        if (!$cache_enabled || $update_cache) {
                            $markdown_text = file_get_contents($full_doc_path);
                            $parse_title = function ($full_doc_path) {
                                        preg_match('/^#\s+(.)[\n|\r]$/', $markdown_text, $matches);
                                        return is_null($matches) ? '' : $matches[1];
                                    };
                            $parsed_data = array();
                            $parsed_data['content'] = MarkdownExtended($markdown_text);
                            $parsed_data['mtime'] = filemtime($full_doc_path);
                            $parsed_data['title'] = $parse_title($full_doc_path);
                            if($cache_enabled){
                                Cache::put($cache_key, $parsed_data, Config::get('docs::options.cache_expiry'));
                            }
                        }
                        return $parsed_data;
                    };

            $source_path = Bundle::path('docs') . 'source';
            $doc_file = false;
            if (empty($section)) {
                $doc_file = "home";
            }
            else {
                $doc_file = rtrim($section . '/' . $page, ' /');
            }

            if (!is_file("$source_path/$doc_file.md")) {
                // Check for a home file
                if (is_file("$source_path/$doc_file/home.md")) {
                    $doc_file = "$doc_file/home";
                }
                else {
                    $doc_file = false;
                }
            }

            if (empty($doc_file)) {
                return Response::error('404');
            }

            $toc = $get_markup('contents');
            $doc = $get_markup($doc_file);

            return View::make('docs::index', array(
                        'title' => $doc['title'],
                        'toc' => $toc['content'],
                        'doc' => $doc['content']
                    ));
        });

View::composer('docs::index', function($view) {
            Asset::container('header1')->bundle('docs');
            Asset::container('header1')->add('bootstrap-css', 'bootstrap/css/bootstrap.min.css');
            Asset::container('header1')->add('docs-css','css/docs.css');
            Asset::container('header2')->bundle('docs');
            Asset::container('header2')->add('bootstrap-responsive-css', 'bootstrap/css/bootstrap-responsive.min.css', 'bootstrap-css');
            Asset::container('header2')->add('prettify-css', 'google-code-prettify/prettify.css');
			Asset::container('header2')->add('uitotop-css', 'ui.totop/ui.totop.css');
            Asset::container('header2')->add('modernizer','js/modernizr-2.5.2.min.js');
            
            Asset::container('footer1')->bundle('docs');
            Asset::container('footer1')->add('jquery','js/jquery-1.7.1.min.js');
            Asset::container('footer1')->add('jquery.totop','ui.totop/jquery.totop.js','jquery');
            Asset::container('footer1')->add('bootstrap.js','bootstrap/js/bootstrap.js','jquery');
            Asset::container('footer1')->add('masonry','js/jquery.masonry.min.js','jquery');
            Asset::container('footer1')->add('prettify','google-code-prettify/prettify.js','jquery');
            Asset::container('footer1')->add('main','js/main.js','jquery');
        });
