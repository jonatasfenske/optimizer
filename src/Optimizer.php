<?php

declare(strict_types=1);

namespace JonatasFenske\Optimizer;

/**
 * Otimizador de meta tags SEO/SMO modernizado para PHP 8.5
 * Gera Open Graph, Twitter Cards, Schema.org e meta tags padrão com tipagem estrita
 */
class Optimizer extends MetaTags
{
    /**
     * Configura meta tags completas de SEO e redes sociais
     *
     * @param string $title Título da página
     * @param string $description Descrição/meta description
     * @param string $url URL canônica
     * @param string $image URL da imagem de compartilhamento
     * @param bool $follow Se true, permite indexação (index,follow); se false, noindex,nofollow
     * @return static
     */
    public function optimize(
        string $title,
        string $description,
        string $url,
        string $image,
        bool $follow = true,
    ): static {
        $this->data($title, $description, $url, $image);

        $filteredTitle = $this->filter($title);
        $filteredDesc = $this->filter($description);

        $this->buildTag('title', $title);
        $this->buildMeta('name', ['description' => $filteredDesc]);
        $this->buildMeta('name', ['robots' => $follow ? 'index, follow' : 'noindex, nofollow']);
        $this->buildLink('canonical', $url);

        foreach ($this->tags as $meta => $prefix) {
            $this->buildMeta($meta, [
                "{$prefix}:title" => $filteredTitle,
                "{$prefix}:description" => $filteredDesc,
                "{$prefix}:url" => $url,
                "{$prefix}:image" => $image,
            ]);
        }

        $this->buildMeta('itemprop', [
            'name' => $filteredTitle,
            'description' => $filteredDesc,
            'url' => $url,
            'image' => $image,
        ]);

        return $this;
    }

    /**
     * Define informações do publisher para Facebook
     *
     * @param string $fbPage Slug ou ID da página do Facebook
     * @param string|null $fbAuthor Perfil do autor (opcional)
     * @return static
     */
    public function publisher(string $fbPage, ?string $fbAuthor = null): static
    {
        $this->buildMeta('property', [
            'article:publisher' => "https://www.facebook.com/{$fbPage}",
        ]);

        if ($fbAuthor !== null) {
            $this->buildMeta('property', [
                'article:author' => "https://www.facebook.com/{$fbAuthor}",
            ]);
        }

        return $this;
    }

    /**
     * Configura Open Graph completo
     *
     * @param string $siteName Nome do site
     * @param string $locale Locale (padrão: pt_BR)
     * @param string $schema Tipo de conteúdo (article, product, etc.)
     * @return static
     */
    public function openGraph(
        string $siteName,
        string $locale = 'pt_BR',
        string $schema = 'article',
    ): static {
        $this->buildMeta('property', [
            'og:type' => $schema,
            'og:site_name' => $this->filter($siteName),
            'og:locale' => $locale,
        ]);

        return $this;
    }

    /**
     * Configura Twitter Card
     *
     * @param string $creator Handle do criador (@usuario)
     * @param string $site Handle do site (@site)
     * @param string $domain Domínio do site
     * @param string|null $card Tipo de card (summary, summary_large_image, etc.)
     * @return static
     */
    public function twitterCard(
        string $creator,
        string $site,
        string $domain,
        ?string $card = null,
    ): static {
        $this->buildMeta('name', [
            'twitter:card' => $card ?? 'summary_large_image',
            'twitter:site' => $site,
            'twitter:creator' => $creator,
            'twitter:domain' => $domain,
        ]);

        return $this;
    }

    /**
     * Configura identificação do Facebook (app_id OU admins, não ambos)
     *
     * @param string|null $appId Facebook App ID
     * @param array<string>|null $admins Lista de IDs de administradores
     * @return static
     */
    public function facebook(?string $appId = null, ?array $admins = null): static
    {
        if ($appId !== null) {
            $fb = $this->meta->addChild('meta');
            $fb->addAttribute('property', 'fb:app_id');
            $fb->addAttribute('content', $appId);
            return $this;
        }

        if ($admins !== null && $admins !== []) {
            foreach ($admins as $admin) {
                $fb = $this->meta->addChild('meta');
                $fb->addAttribute('property', 'fb:admins');
                $fb->addAttribute('content', $admin);
            }
        }

        return $this;
    }
}