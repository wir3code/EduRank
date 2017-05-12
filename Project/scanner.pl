#!/usr/bin/perl -w

#Spencer Brydges 
#Shefali Chohan 
#The following script will be cron'd to execute after hour
#This simple script reads the log files generated by the website.
#If a user attempts to hack into the site too many times,
#the website will add their IP to the files followed by a "Request Server Ban"
#This script, in turn, will immediately firewall the IP, dropping packets to all source ports

sub get_file_contents
{
        my($filename) = $_[0];
        open FH, $filename or die "Failed to open $filename for reading\n";
        my @contents = <FH>;
        close FH;
        return @contents;
}



@contents = get_file_contents 'logs.txt';

if(scalar(@contents) > 0)
{
        foreach $line(@contents)
        {
                if($line =~ /(\d+\.\d+\.*\d*\.*\d*).*Request Server Ban/i)
                {
                        print "Executing IPTables on $1...\n";
                        system("iptables -I INPUT -s $1 -j DROP");
                }
        }
}
else
{
        print "good";
}

open FH, '>logs.txt';
print "", FH;
close FH;