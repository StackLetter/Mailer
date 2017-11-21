<?php

namespace Newsletter;

use Nette\Mail\Message;
use Nette\Utils\Strings;

class CustomMessage extends Message{
    public function setHtmlBody($html, $basePath = null)
    {
        $html = (string) $html;

        if ($basePath) {
            $cids = [];
            $matches = Strings::matchAll(
                $html,
                '#
					(<img[^<>]*\s src\s*=\s*
					|<body[^<>]*\s background\s*=\s*
					|<[^<>]+\s style\s*=\s* ["\'][^"\'>]+[:\s] url\(
					|<style[^>]*>[^<]+ [:\s] url\()
					(["\']?)(?![a-z]+:|[/\\#])([^"\'>)\s]+)
				#ix',
                PREG_OFFSET_CAPTURE
            );
            foreach (array_reverse($matches) as $m) {
                $file = rtrim($basePath, '/\\') . '/' . (isset($m[4]) ? $m[4][0] : urldecode($m[3][0]));
                if (!isset($cids[$file])) {
                    $cids[$file] = substr($this->addEmbeddedFile($file)->getHeader('Content-ID'), 1, -1);
                }
                $html = substr_replace($html,
                    "{$m[1][0]}{$m[2][0]}cid:{$cids[$file]}",
                    $m[0][1], strlen($m[0][0])
                );
            }
        }

        if ($this->getSubject() == null) { // intentionally ==
            $html = Strings::replace($html, '#<title>(.+?)</title>#is', function ($m) {
                $this->setSubject(html_entity_decode($m[1], ENT_QUOTES, 'UTF-8'));
            });
        }

        $this->htmlBody = ltrim(str_replace("\r", '', $html), "\n");

        if ($this->getBody() === '' && $html !== '') {
            $this->setBody($this->buildText($html));
        }

        return $this;
    }
}
