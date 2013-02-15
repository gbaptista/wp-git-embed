WP-Git-Embed
--------

Download: [http://wordpress.org/extend/plugins/wp-git-embed/](http://wordpress.org/extend/plugins/wp-git-embed/)

Demo
--------

[WP-Git-Embed: Incluindo Arquivos do GitHub, Gist ou Bitbucket no seu Post do WordPress](http://gbaptista.com/2013/02/13/wp-git-embed-incluindo-arquivos-do-github-gist-bitbucket-no-seu-post-do-wordpress/)


Usage
--------

### With [WP-Syntax](http://wordpress.org/extend/plugins/wp-syntax/)

```html
<pre lang="ruby" line="1">
[git:https://github.com/rails/rails/blob/3-1-stable/railties/lib/rails/version.rb]
</pre>
```

### With [SyntaxHighlighter](http://wordpress.org/extend/plugins/syntaxhighlighter/)

```html
[sourcecode language="ruby"]
[git:https://github.com/rails/rails/blob/3-1-stable/railties/lib/rails/version.rb]
[/sourcecode]
```

### GitHub

```
[git:https://github.com/rails/rails/blob/3-1-stable/railties/lib/rails/version.rb]
```

```
[git:https://github.com/rails/rails/blob/3-1-stable/railties/lib/rails/version.rb{8}]
```

```
[git:https://github.com/rails/rails/blob/3-1-stable/railties/lib/rails/version.rb{6:10}]
```

### GitHub Gist

```
[git:https://gist.github.com/gbaptista/4958597#file-a.rb]
```

```
[git:https://gist.github.com/gbaptista/4958597#file-a.rb{2:3}]
```

### Bitbucket

```
[git:https://bitbucket.org/gbaptista/test/src/7a350b304059b49616fee1870c59f53a8149db4a/lorem/file.rb?at=master]
```

```
[git:https://bitbucket.org/gbaptista/test/src/7a350b304059b49616fee1870c59f53a8149db4a/lorem/file.rb]
```

```
[git:https://bitbucket.org/gbaptista/test/src/7a350b304059b49616fee1870c59f53a8149db4a/lorem/file.rb{2,3}]
```
