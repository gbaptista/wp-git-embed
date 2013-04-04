WP-Git-Embed
--------

WordPress Plugin: Embed GitHub/Gist/Bitbucket/Whatever files.

Download: [http://wordpress.org/extend/plugins/wp-git-embed/](http://wordpress.org/extend/plugins/wp-git-embed/)

Demo
--------

[WP-Git-Embed: Incluindo Arquivos do GitHub, Gist ou Bitbucket no seu Post do WordPress](http://gbaptista.com/2013/02/13/wp-git-embed-incluindo-arquivos-do-github-gist-bitbucket-no-seu-post-do-wordpress/)


Usage
--------

### With [WP-Syntax](http://wordpress.org/extend/plugins/wp-syntax/)

```html
[git:pre_ruby@https://github.com/rails/rails/blob/3-1-stable/railties/lib/rails/version.rb]
```

### With [SyntaxHighlighter](http://wordpress.org/extend/plugins/syntaxhighlighter/)

```html
[git:sourcecode_ruby@https://github.com/rails/rails/blob/3-1-stable/railties/lib/rails/version.rb]
```

### GitHub

```
[git:pre_ruby@https://github.com/rails/rails/blob/3-1-stable/railties/lib/rails/version.rb]
```

```
[git:pre_ruby@https://github.com/rails/rails/blob/3-1-stable/railties/lib/rails/version.rb{8}]
```

```
[git:pre_ruby@https://github.com/rails/rails/blob/3-1-stable/railties/lib/rails/version.rb{6:10}]
```

### GitHub Gist

```
[git:pre_ruby@https://gist.github.com/gbaptista/4958597#file-a.rb]
```

```
[git:pre_ruby@https://gist.github.com/gbaptista/4958597#file-a.rb{2:3}]
```

### Bitbucket

```
[git:pre_ruby@https://bitbucket.org/gbaptista/test/src/7a350b304059b49616fee1870c59f53a8149db4a/lorem/file.rb]
```

```
[git:pre_ruby@https://bitbucket.org/gbaptista/test/src/7a350b304059b49616fee1870c59f53a8149db4a/lorem/file.rb{2:3}]
```

### Custom Files

```
[file:http://your-site/file.txt]
```

```
[file:pre_bash@http://gbaptista.com/custom_file.sh{1:8}]
```

```
[file:/var/www/file.txt]
```
