package tn.esprit.services;

import java.sql.SQLException;
import java.util.List;

public interface IService<T> {
    void add(T t) throws SQLException;
    void delete(int id);
    void update(int id,T t);
    List<T> getAll();

}
