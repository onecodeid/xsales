READS SQL DATA
begin	declare number varchar(50);
   declare prefix varchar(50);
   declare prefix_date  varchar(50);
   declare sufix varchar(50);
   declare counter int;
   declare dgt int;
   declare rst varchar(5);
   declare udt datetime;


   select S_NumberingPrefix, S_NumberingPrefixDate, S_NumberingSufix, S_NumberingCounter, S_NumberingDigit, S_NumberingReset, 
   S_NumberingLastUpdated	
   into prefix, prefix_date, sufix, counter, dgt, rst, udt	
   from s_numbering	where S_NumberingType = type	for update;

   if rst = 'D' then
      if date_format(udt, '%Y-%m-%d') <> date_format(now(), '%Y-%m-%d') then
	 set counter = 1;
      end if;
   elseif rst = 'M' then
      if date_format(udt, '%Y-%m') <> date_format(now(), '%Y-%m') then
	 set counter = 1;
      end if;
   elseif rst = 'Y' then
      if date_format(udt, '%Y') <> date_format(now(), '%Y') then
	 set counter = 1;
      end if;
   end if;

   set number = '';

   
        if prefix is not null and prefix <> '' then
            set number = trim(prefix);
        end if;
        if prefix_date is not null and prefix_date <> '' then
            set number = concat(trim(number),date_format(now(),prefix_date));
        end if;

        if sufix is not null and sufix <> '' then
            set number = concat(trim(number),trim(sufix));
        end if;
    

    if type = "DO"
        OR type = "SO"
        OR type = "INV"
        OR type = "SF"
        OR type = "LEAD" 
        OR type = "PO" 
        OR type = "RO" THEN
        set number = concat(lpad(counter,dgt,'0'), trim(number));
    else
        set number = concat(trim(number), lpad(counter,dgt,'0'));
    end if;

--    if type = 'L' then
--       set @branch_code = (select M_BranchCode from m_branch where M_BranchIsActive = 'Y' 
-- 	 and M_BranchIsDefault = 'Y' limit 0,1 );
--       set number = concat(number,@branch_code);
--    end if;
   update s_numbering set S_NumberingCounter = counter + 1, S_NumberingLastUpdated = NOW() where S_NumberingType=type;
   return trim(number);

END