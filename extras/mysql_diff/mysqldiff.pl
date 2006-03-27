#!/usr/bin/perl -w
#
# mysqldiff
#
# Utility to compare table definitions in two MySQL databases,
# and output a patch in the format of ALTER TABLE statements
# which converts the first database structure into in the second.
#
# Developed as part of the http://www.guideguide.com/ project.
# If you like hacking Perl in a cool environment, come and work for us!
#
# See http://www.new.ox.ac.uk/~adam/computing/mysqldiff/ for the
# latest version.
#
# Copyright (c) 2000 Adam Spiers <adam@spiers.net>. All rights
# reserved. This program is free software; you can redistribute it
# and/or modify it under the same terms as Perl itself.
#

use strict;

use vars qw($VERSION);
$VERSION = '0.25';

require 5.004;

use Carp qw(:DEFAULT cluck);
use FindBin qw($Script);
use Getopt::Long;

my %opts = ();
GetOptions(\%opts, "help|?", "debug|d:i",
           "no-old-defs|n", "only-both|o", "table-re|t=s",
           "host|h=s",   "user|u=s",   "password|p:s",
           "host1|h1=s", "user1|u1=s", "password1|p1:s",
           "host2|h2=s", "user2|u2=s", "password2|p2:s",
           "tolerant|i"
          );

if (@ARGV != 2 or $opts{help}) {
  usage();
  exit 1;
}

$opts{debug}++ if exists $opts{debug} && $opts{debug} == 0;
my $debug = $opts{debug} || 0;

my $table_re;
$table_re = qr/$opts{'table-re'}/ if $opts{'table-re'};

my @db = ();
for my $num (0, 1) {
  $db[$num] = parse_arg($ARGV[$num], $num);
}

diff_dbs(@db);

exit 0;

##############################################################################
#

sub usage {
  print STDERR @_, "\n" if @_;
  die <<EOF;
Usage: $Script [ options ] <database1> <database2>

Options:
  -?,  --help             show this help
  -d,  --debug[=N]        enable debugging [level N, default 1]
  -o,  --only-both        only output changes for tables in both databases
  -n,  --no-old-defs      suppress comments describing old definitions
  -t,  --table-re=REGEXP  restrict comparisons to tables matching REGEXP
  -i,  --tolerant         ignore DEFAULT and formatting changes

  -h,  --host=...         connect to host
  -u,  --user=...         user for login if not current user
  -p,  --password[=...]   password to use when connecting to server

for <databaseN> only, where N == 1 or 2,
  -hN, --hostN=...        connect to host
  -uN, --userN=...        user for login if not current user
  -pN, --passwordN[=...]  password to use when connecting to server

Databases can be either files or database names.
If there is an ambiguity, the file will be preferred;
to prevent this prefix the database argument with `db:'.
EOF
}

sub diff_dbs {
  my @db = @_;

  debug(1, "comparing databases\n");

  my @changes = ();

  foreach my $table1 ($db[0]->tables()) {
    my $name = $table1->name();
    if ($table_re && $name !~ $table_re) {
      debug(2, "  table `$name' didn't match $opts{'table-re'}; ignoring\n");
      next;
    }
    debug(2, "  looking at tables called `$name'\n");
    if (my $table2 = $db[1]->table_by_name($name)) {
      debug(4, "    comparing tables called `$name'\n");
      push @changes, diff_tables($table1, $table2);
    }
    else {
      debug(3, "    table `$name' dropped\n");
      push @changes, "DROP TABLE $name;\n\n"
        unless $opts{'only-both'};
    }
  }

  foreach my $table2 ($db[1]->tables()) {
    my $name = $table2->name();
    if ($table_re && $name !~ $table_re) {
      debug(2, "  table `$name' matched $opts{'table-re'}; ignoring\n");
      next;
    }
    if (! $db[0]->table_by_name($name)) {
      debug(3, "    table `$name' added\n");
      push @changes, $table2->def() . "\n"
        unless $opts{'only-both'};
    }
  }

  if (@changes) {
    diff_banner(@db);
    print @changes;
  }
}

sub diff_banner {
  my @db = @_;

  my $summary1 = $db[0]->summary();
  my $summary2 = $db[1]->summary();

  my $now = scalar localtime();
  print <<EOF;
## mysqldiff $VERSION
## 
## run on $now
##
## --- $summary1
## +++ $summary2

EOF
}

sub diff_tables {
  my @changes = (diff_fields(@_),
                 diff_indices(@_),
                 diff_primary_key(@_));
  if (@changes) {
    $changes[-1] .= "\n";
  }
  return @changes;
}

sub diff_fields {
  my ($table1, $table2) = @_;

  my $name1 = $table1->name();

  my %fields1 = %{ $table1->fields() };
  my %fields2 = %{ $table2->fields() };

  my @changes = ();
  
  foreach my $field (keys %fields1) {
    my $f1 = $fields1{$field};
    if (my $f2 = $fields2{$field}) {
      if ($f1 ne $f2) {
        if (not $opts{tolerant} or (($f1 !~ m/$f2\(\d+,\d+\)/)         and
                                    ($f1 ne "$f2 DEFAULT '' NOT NULL") and
                                    ($f1 ne "$f2 NOT NULL")
                                   ))
        {
          debug(4, "      field `$field' changed\n");

          my $change = "ALTER TABLE $name1 CHANGE COLUMN $field $field $f2;";
          $change .= " # was $f1" unless $opts{'no-old-defs'};
          $change .= "\n";
          push @changes, $change;
        }
      }
    }
    else {
      debug(4, "      field `$field' removed\n");
      my $change = "ALTER TABLE $name1 DROP COLUMN $field;";
      $change .= " # was $fields1{$field}" unless $opts{'no-old-defs'};
      $change .= "\n";
      push @changes, $change;
    }
  }

  foreach my $field (keys %fields2) {
    if (! $fields1{$field}) {
      debug(4, "      field `$field' added\n");
      push @changes, "ALTER TABLE $name1 ADD COLUMN $field $fields2{$field};\n";
    }
  }

  return @changes;
}

sub diff_indices {
  my ($table1, $table2) = @_;

  my $name1 = $table1->name();

  my %indices1 = %{ $table1->indices() };
  my %indices2 = %{ $table2->indices() };

  my @changes = ();

  foreach my $index (keys %indices1) {
    my $old_type = $table1->is_unique_index($index) ? 'UNIQUE' : 'INDEX';

    if ($indices2{$index}) {
      if ($indices1{$index} ne $indices2{$index} ||
          ($table1->is_unique_index($index)
             xor
           $table2->is_unique_index($index)))
      {
        debug(4, "      index `$index' changed\n");
        my $new_type = $table2->is_unique_index($index) ? 'UNIQUE' : 'INDEX';

        my $changes = '';
        if ($indices1{$index}) {
          $changes .= "ALTER TABLE $name1 DROP INDEX $index;";
          $changes .= " # was $old_type ($indices1{$index})" unless $opts{'no-old-defs'};
          $changes .= "\n";
        }

        $changes .= <<EOF;
ALTER TABLE $name1 ADD $new_type $index ($indices2{$index});
EOF
        push @changes, $changes;
      }
    }
    else {
      debug(4, "      index `$index' removed\n");
      my $change = "ALTER TABLE $name1 DROP INDEX $index;";
      $change .= " # was $old_type ($indices1{$index})" unless $opts{'no-old-defs'};
      $change .= "\n";
      push @changes, $change;
    }
  }

  foreach my $index (keys %indices2) {
    if (! $indices1{$index}) {
      debug(4, "      index `$index' added\n");
      push @changes,
           "ALTER TABLE $name1 ADD INDEX $index ($indices2{$index});\n";
    }
  }

  return @changes;
}

sub diff_primary_key {
  my ($table1, $table2) = @_;

  my $name1 = $table1->name();

  my $primary1 = $table1->primary_key();
  my $primary2 = $table2->primary_key();

  my @changes = ();
  if (($primary1 xor $primary2) || ($primary1 && ($primary1 ne $primary2))) {
    debug(4, "      primary key changed\n");
    my $change = "ALTER TABLE $name1 DROP PRIMARY KEY;";
    $change .= " # was ($primary1)" unless $opts{'no-old-defs'};
    $change .= <<EOF;

ALTER TABLE $name1 ADD PRIMARY KEY ($primary2);
EOF
    push @changes, $change;
  }

  return @changes;
}

##############################################################################

sub auth_args {
  my %auth = @_;
  my $args = '';
  for my $arg (qw/host user password/) {
    $args .= " --$arg=$auth{$arg}" if $auth{$arg};
  }
  return $args;
}

sub available_dbs {
  my %auth = @_;
  my $args = auth_args(%auth);
  
  # evil but we don't use DBI because I don't want to implement -p properly
  # not that this works with -p anyway ...
  open(MYSQLSHOW, "mysqlshow$args |")
    or die "Couldn't execute `mysqlshow$args': $!\n";
  my @dbs = ();
  while (<MYSQLSHOW>) {
    next unless /^\| (\w+)/;
    push @dbs, $1;
  }
  close(MYSQLSHOW);

  return map { $_ => 1 } @dbs;
}

sub parse_arg {
  my ($arg, $num) = @_;

  debug(1, "parsing arg $num: `$arg'\n");

  my $authnum = $num + 1;
  
  my %auth = ();
  for my $auth (qw/host user password/) {
    $auth{$auth} = $opts{"$auth$authnum"} || $opts{$auth};
    delete $auth{$auth} unless $auth{$auth};
  }

  if ($arg =~ /^db:(.*)/) {
    return new MySQL::Database(db => $1, %auth);
  }

  if ($opts{"host$authnum"} ||
      $opts{"user$authnum"} ||
      $opts{"password$authnum"})
  {
    return new MySQL::Database(db => $arg, %auth);
  }

  if (-e $arg) {
    return new MySQL::Database(file => $arg, %auth);
  }

  my %dbs = available_dbs(%auth);
  debug(2, "  available databases: ", (join ', ', keys %dbs), "\n");

  if ($dbs{$arg}) {
    return new MySQL::Database(db => $arg, %auth);
  }

  usage("`$arg' is not a valid file or database.\n");
  exit 1;
}

sub debug {
  my $level = shift;
  print STDERR @_ if $debug >= $level && @_;
}

##############################################################################
#

package MySQL::Database;

use Carp qw(:DEFAULT cluck);

sub debug { &::debug }

sub new {
  my $class = shift;
  my %p = @_;
  my $self = {};
  bless $self, ref $class || $class;

  debug(2, "  constructing new MySQL::Database\n");

  my $args = &::auth_args(%p);
  debug(3, "    auth args: $args\n");

  if ($p{file}) {
    $self->{_source} = { file => $p{file} };
    debug(3, "    fetching table defs from file $p{file}\n");

# FIXME: option to avoid create-and-dump bit
    # create a temporary database using defs from file ...
    # hopefully the temp db is unique!
    my $temp_db = sprintf "test_mysqldiff_temp_%d_%d", time(), $$;
    debug(3, "    creating temporary database $temp_db\n");

    open(DEFS, $p{file})
      or die "Couldn't open `$p{file}': $!\n";
    open(MYSQL, "| mysql $args")
      or die "Couldn't execute `mysql$args': $!\n";
    print MYSQL <<EOF;
CREATE DATABASE $temp_db;
USE $temp_db;
EOF
    print MYSQL <DEFS>;
    close(DEFS);
    close(MYSQL);

    # ... and then retrieve defs from mysqldump.  Hence we've used
    # MySQL to massage the defs file into canonical form.
    $self->_get_defs($temp_db, $args);

    debug(3, "    dropping temporary database $temp_db\n");
    open(MYSQL, "| mysql $args")
      or die "Couldn't execute `mysql$args': $!\n";
    print MYSQL "DROP DATABASE $temp_db;\n";
    close(MYSQL);
  }
  elsif ($p{db}) {
    $self->{_source} = { db => $p{db}, auth => $args };
    debug(3, "    fetching table defs from db $p{db}\n");
    $self->_get_defs($p{db}, $args);
  }
  else {
    confess "MySQL::Database::new called without db or file params";
  }

  $self->_parse_defs();

  return $self;
}

sub _get_defs {
  my $self = shift;
  my ($db, $args) = @_;

  open(MYSQLDUMP, "mysqldump -d $args $db |")
      or die "Couldn't read ${db}'s table defs via mysqldump: $!\n";
  debug(3, "    running mysqldump -d $args $db\n");
  $self->{_defs} = [ <MYSQLDUMP> ];
  close(MYSQLDUMP);
}

sub _parse_defs {
  my $self = shift;

  return if $self->{_tables};

  debug(3, "    parsing table defs\n");
  my $defs = join '', grep ! /^\s*(--|\#)/, @{$self->{_defs}};
  my @tables = split /(?=^\s*create\s+table\s+)/im, $defs;
  foreach my $table (@tables) {
    next unless $table =~ /create\s+table/i;
    my $obj = MySQL::Table->new(source => $self->{_source},
                                def => $table);
    push @{$self->{_tables}}, $obj;
    $self->{_by_name}{$obj->name()} = $obj;
  }
}

sub tables {
  return @{$_[0]->{_tables}};
}

sub table_by_name {
  my $self = shift;
  my ($name) = @_;
  return $self->{_by_name}{$name};
}

sub summary {
  my $self = shift;
  
  if ($self->{_source}{file}) {
    return "file: " . $self->{_source}{file};
  }
  elsif ($self->{_source}{db}) {
    my $args = $self->{_source}{auth};
    $args =~ tr/-//d;
    $args =~ s/\bpassword=\S+//;
    $args =~ s/^\s*(.*?)\s*$/$1/;
    my $summary = "  db: " . $self->{_source}{db};
    $summary .= " ($args)" if $args;
    return $summary;
  }
  else {
    return 'unknown';
  }
}

##############################################################################
#

package MySQL::Table;

use Carp qw(:DEFAULT cluck);

sub debug { &::debug }

sub new {
  my $class = shift;
  my %p = @_;
  my $self = {};
  bless $self, ref $class || $class;

  debug(4, "      constructing new MySQL::Table\n");

  if (! $p{def}) {
    croak "MySQL::Table::new called without def params";
  }

  $self->parse($p{def});

  $self->{_source} = $p{source};

  return $self;
}

sub parse {
  my $self = shift;
  my ($def) = @_;

  $def =~ s/\n+/\n/;
  $self->{_def} = $def;
  $self->{_lines} = [ grep ! /^\s*$/, split /(?=^)/m, $def ];
  my @lines = @{$self->{_lines}};

  debug(5, "        parsing table def\n");

  my $name;
  if ($lines[0] =~ /^\s*create\s+table\s+(\S+)\s+\(\s*$/i) {
    $name = $self->{_name} = $1;
    debug(5, "        got table name `$name'\n");
    shift @lines;
  }
  else {
    croak "couldn't figure out table name";
  }

  while (@lines) {
    $_ = shift @lines;
    s/^\s*(.*?),?\s*$/$1/; # trim whitespace and trailing commas
    if (/^\);$/) {
      last;
    }

    if (/^PRIMARY\s+KEY\s+(.+)$/) {
      my $primary = $1;
      croak "two primary keys in table `$name': `$primary', `",
            $self->{_primary_key}, "'\n"
        if $self->{_primary_key};
      $self->{_primary_key} = $primary;
      debug(6, "          got primary key `$primary'\n");
      next;
    }

    if (/^(KEY|UNIQUE)\s+(\S+?)\s+\((.*)\)$/) {
      my ($type, $key, $val) = ($1, $2, $3);
      croak "index `$key' duplicated in table `$name'\n"
        if $self->{_indices}{$key};
      $self->{_indices}{$key} = $val;
      $self->{_unique_index}{$key} = ($type =~ /unique/i) ? 1 : 0;
      debug(6, "          got ",
               ($type =~ /unique/i) ? 'unique ' : '',
               "index key `$key': ($val)\n");
      next;
    }

    if (/^(\S+)\s*(.*)/) {
      my ($field, $def) = ($1, $2);
      croak "definition for field `$field' duplicated in table `$name'\n"
        if $self->{_fields}{$field};
      $self->{_fields}{$field} = $def;
      debug(6, "          got field def `$field': $def\n");
      next;
    }

    croak "unparsable line in definition for table `$name':\n$_";
  }

  if (@lines) {
    my $name = $self->name();
    warn "table `$name' had trailing garbage:\n", join '', @lines;
  }
}

sub def             { $_[0]->{_def}                 }
sub name            { $_[0]->{_name}                }
sub source          { $_[0]->{_source}              }
sub fields          { $_[0]->{_fields}  || {}       }
sub indices         { $_[0]->{_indices} || {}       }
sub primary_key     { $_[0]->{_primary_key}         }
sub is_unique_index { $_[0]->{_unique_index}{$_[1]} }
